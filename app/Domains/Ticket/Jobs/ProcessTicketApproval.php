<?php

namespace App\Domains\Ticket\Jobs;

use App\Domains\Ticket\Events\TicketStatusChanged;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Repositories\WebServiceLogRepository;
use App\Domains\Ticket\States\Approved;
use App\Domains\Ticket\States\Failed;
use App\Domains\Ticket\States\PendingLevel2;
use App\Domains\Ticket\States\Processing;
use App\Http\Controllers\Api\V1\FakeWebServiceController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ProcessTicketApproval
 *
 * Handles asynchronous processing of ticket approval by calling a web service.
 * Implements retry logic with exponential backoff, concurrency locking,
 * attempt logging, and state transitions.
 *
 * @package App\Domains\Ticket\Jobs
 */
class ProcessTicketApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * The ticket being processed.
     *
     * @var Ticket
     */
    public Ticket $ticket;

    /**
     * ID of the user who initiated the approval.
     *
     * @var int
     */
    public int $userId;

    /**
     * Current attempt number (starts at 0, incremented in handle).
     *
     * @var int
     */
    public int $attemptNumber;

    /**
     * Maximum number of job attempts (0 = unlimited).
     *
     * @var int
     */
    public $tries = 0;

    /**
     * Delay between retries in seconds (1 hour).
     *
     * @var int
     */
    public $backoff = 3600;

    /**
     * Create a new job instance.
     *
     * @param Ticket $ticket The ticket to process.
     * @param int $userId The ID of the user who triggered the approval.
     */
    public function __construct(Ticket $ticket, int $userId)
    {
        $this->ticket = $ticket;
        $this->userId = $userId;
        $this->attemptNumber = 0;
    }

    /**
     * Execute the job.
     *
     * - Skips if ticket is already approved.
     * - Skips if ticket is not in a valid state (PendingLevel2, Processing, Failed).
     * - Acquires a cache lock to prevent concurrent processing.
     * - Calls the fake web service.
     * - Logs the attempt.
     * - Transitions to Approved on success, or to Failed on failure with retry.
     *
     * @param WebServiceLogRepository $logRepository Repository for logging web service attempts.
     * @return void
     */
    public function handle(WebServiceLogRepository $logRepository): void
    {
        $currentStatus = $this->ticket->status->getValue();

        $attemptCount = $logRepository->attemptCount($this->ticket->id);
        $this->attemptNumber = $attemptCount + 1;

        if ($currentStatus === Approved::class) {
            Log::info("Ticket {$this->ticket->id} already approved. Deleting job.");
            $this->delete();
            return;
        }

        if (!in_array($currentStatus, [
            PendingLevel2::class,
            Processing::class,
            Failed::class,
        ])) {
            Log::info("Ticket {$this->ticket->id} is no longer pending approval. Job skipped.");
            $this->delete();
            return;
        }

        // Prevent concurrent processing with cache lock
        $lock = Cache::lock("ticket_approval_{$this->ticket->id}", 60);

        if (!$lock->get()) {
            Log::warning("Another job is already processing ticket {$this->ticket->id}. Releasing for later.");
            $this->release(60);
            return;
        }

        try {
            $responseBody = null;
            $success = false;

            try {
                $result = FakeWebServiceController::call([
                    'ticket_id' => $this->ticket->id,
                    'subject'   => $this->ticket->subject,
                ]);
                $success = $result['success'];
                $responseBody = $result['response'];
            } catch (\Exception $e) {
                Log::error("WebService call failed for ticket {$this->ticket->id}: " . $e->getMessage());
                $success = false;
                $responseBody = $e->getMessage();
            }

            // Log the attempt
            $logRepository->create([
                'ticket_id'      => $this->ticket->id,
                'attempt_number' => $this->attemptNumber,
                'status'         => $success ? 'success' : 'failed',
                'response'       => $responseBody ?? 'Unknown error',
            ]);

            if ($success) {
                DB::transaction(function () {
                    $oldStatus = $this->ticket->status->getValue();
                    $this->ticket->status->transitionTo(Approved::class);
                    event(new TicketStatusChanged($this->ticket, $oldStatus, Approved::class, $this->userId));
                });
                Log::info("Ticket {$this->ticket->id} approved successfully after {$this->attemptNumber} attempt(s).");
                return;
            } else {
                DB::transaction(function () {
                    $oldStatus = $this->ticket->status->getValue();
                    $this->ticket->status->transitionTo(Failed::class);
                    event(new TicketStatusChanged($this->ticket, $oldStatus, Failed::class, $this->userId));
                });
                Log::error("Ticket {$this->ticket->id} failed after {$this->attemptNumber} attempts. Moved to Failed.");

                $this->release($this->backoff);
                return;
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception The exception that caused the failure.
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("ProcessTicketApproval job failed for ticket {$this->ticket->id}: " . $exception->getMessage());
    }
}
