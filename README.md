<h1 align="center">🎫 Ticket Chain</h1>

<p align="center">
Modern Ticket Management System with Chain-Based Replies
</p>

<p align="center">
Built with Laravel, Vue, Vite and TailwindCSS
</p>

<hr/>

<h2>📌 Overview</h2>

<p>
Ticket Chain is a scalable and maintainable ticket management system designed to handle customer support conversations using a <b>chain-based reply structure</b>.
</p>

<p>
The system is built with modern web technologies and follows clean architecture principles to ensure long-term maintainability and extensibility.
</p>

<ul>
<li>Threaded ticket conversations</li>
<li>Reply chaining instead of flat comments</li>
<li>Task extraction from replies</li>
<li>Scalable and modular structure</li>
</ul>

<hr/>

<h2>🚀 Installation & Setup</h2>

<h3>1. Clone Repository</h3>

<pre>
git clone https://github.com/ahmadjam78/ticket-chain.git
cd ticket-chain
</pre>

<h3>2. Install Dependencies</h3>

<b>Backend:</b>
<pre>
composer install
</pre>

<b>Frontend:</b>
<pre>
npm install
</pre>

<h3>3. Configure Environment</h3>

<pre>
cp .env.example .env
php artisan key:generate
</pre>

<p>Update database credentials inside <code>.env</code></p>

<pre>
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_chain
DB_USERNAME=root
DB_PASSWORD=
</pre>

<h3>4. Run Migrations & Seeders</h3>

<pre>
php artisan migrate --seed
</pre>

<h3>5. Run Application</h3>

<b>Backend:</b>
<pre>
php artisan serve
</pre>

<b>Frontend:</b>
<pre>
npm run dev
</pre>

<hr/>

<h2>⚙️ Queue & Events Setup</h2>

<h3>1. Configure Queue</h3>

<pre>
QUEUE_CONNECTION=database
</pre>

<h3>2. Create Queue Tables</h3>

<pre>
php artisan queue:table
php artisan migrate
</pre>

<h3>3. Run Queue Worker</h3>

<pre>
php artisan queue:work
</pre>

<p>Production:</p>

<pre>
php artisan queue:work --daemon
</pre>

<h3>4. Failed Jobs (Recommended)</h3>

<pre>
php artisan queue:failed-table
php artisan migrate
</pre>

<pre>
php artisan queue:failed
php artisan queue:retry all
</pre>

<h3>📡 Events & Listeners</h3>

<h3>Queueable Listener</h3>

<pre>
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTicketNotification implements ShouldQueue
</pre>

<h3>Restart Queue</h3>

<pre>
php artisan queue:restart
</pre>

<hr/>

<h2>🏗️ Project Structure</h2>

<pre>
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
</pre>

<p>
The project follows Laravel's standard structure with separation of concerns across backend and frontend layers.
</p>

<hr/>

<h2>🧠 Architecture</h2>

<p>
The system is built using a layered architecture based on Laravel MVC, enhanced with service-oriented design.
</p>

<h3>Layers</h3>

<ul>
<li><b>Controllers:</b> Handle HTTP requests and responses</li>
<li><b>Models:</b> Represent database entities</li>
<li><b>Services:</b> Encapsulate business logic</li>
<li><b>Form Requests:</b> Handle validation</li>
<li><b>Policies & Gates:</b> Authorization layer</li>
<li><b>Vue Components:</b> Frontend rendering</li>
</ul>

<hr/>

<h2>🎯 Design Patterns</h2>

<ul>
<li><b>MVC Pattern</b> – Core Laravel structure for handling requests, responses and routing</li>

<li><b>Service Layer Pattern</b> – Encapsulates business logic and keeps controllers thin and maintainable</li>

<li><b>Use Case / Action Pattern</b> – Each business operation (e.g. CreateTicket, ReplyToTicket) is implemented as a dedicated action class for better separation and testability</li>

<li><b>Repository Pattern</b> – Abstracts data access logic and provides a clean interface for querying and persisting domain data</li>

<li><b>Data Transfer Object (DTO)</b> – Structured data objects used to transfer data between layers and control input/output consistency</li>

<li><b>Factory Pattern</b> – Used for generating test data and seeding the database</li>

<li><b>Observer Pattern (Event-Driven)</b> – Events and listeners are used to decouple side effects like notifications and background processing</li>

<li><b>Queue Pattern (Asynchronous Processing)</b> – Heavy or time-consuming tasks are executed asynchronously using Laravel queues and jobs</li>

<li><b>State Pattern</b> – Ticket states (e.g. open, pending, closed) are managed using state classes for better control and extensibility</li>

<li><b>Policy Pattern (Authorization)</b> – Access control is handled via policies to enforce business rules and permissions</li>

<li><b>Pipeline / Middleware Pattern</b> – Request filtering and preprocessing (authentication, roles, etc.) using Laravel middleware</li>

<li><b>Resource Pattern (API Transformation)</b> – API responses are transformed using Laravel API Resources to ensure consistent output structure</li>

<li><b>Domain-Oriented Design (DDD-lite)</b> – Code is organized by domain (e.g. Ticket, User) to keep business logic modular, scalable and maintainable</li>

<li><b>Modular Monolith Architecture</b> – The system is structured as a single application but internally divided into independent domain modules</li>

<li><b>Chain Structure Pattern</b> – Ticket replies are modeled as linked chains instead of flat lists, enabling better conversation tracking and bulk operations</li>
</ul>

<h2>🏗️ Architecture Flow</h2>

<p>
The system follows a layered, domain-oriented architecture where each request flows through clearly separated layers.
</p>

<pre>
Client (Vue.js SPA)
        ↓
API Request (HTTP)
        ↓
Route (api.php)
        ↓
Controller (Thin Layer)
        ↓
Action / Use Case
        ↓
Service Layer
        ↓
Domain (Models, States, Policies)
        ↓
Repository Layer
        ↓
Database

----------------------------

Side Effects Flow:

Action / Service
        ↓
Event Dispatch
        ↓
Listener
        ↓
Job (Queue)
        ↓
Async Processing (Email, Notifications, etc.)
</pre>

---

<h3>🔍 Flow Explanation</h3>

<ul>
<li><b>Controller:</b> Receives request and delegates logic to Actions</li>

<li><b>Action (Use Case):</b> Represents a single business operation (e.g. CreateTicket)</li>

<li><b>Service Layer:</b> Handles complex business logic and orchestration</li>

<li><b>Domain Layer:</b> Contains core entities, states, and business rules</li>

<li><b>Repository:</b> Manages data persistence and querying</li>

<li><b>Event System:</b> Decouples side effects from core logic</li>

<li><b>Queue System:</b> Processes heavy operations asynchronously</li>
</ul>

---

<h3>🧠 Key Architectural Concepts</h3>

<ul>
<li><b>Thin Controllers:</b> Controllers contain minimal logic</li>

<li><b>Use Case Driven:</b> Business logic is organized around actions</li>

<li><b>Domain-Oriented Structure:</b> Code is grouped by business domains (Ticket, User)</li>

<li><b>Event-Driven Side Effects:</b> Notifications and async tasks are decoupled</li>

<li><b>Scalable Design:</b> Architecture is ready for future microservice extraction</li>
</ul>

<hr/>

<h2>👤 Default Users</h2>

<h3>🔐 Admin 1</h3>

<pre>
Email: admin-level1@example.com
Password: password
</pre>

<h3>🔐 Admin 2</h3>

<pre>
Email: admin-level2@example.com
Password: password
</pre>

<h3>👥 Customer</h3>

<pre>
Email: customer@example.com
Password: password
</pre>

<p>
<b>Important:</b> Change default credentials immediately after deployment.
</p>

<hr/>

<h2>✨ Features</h2>

<h3>🎫 Ticket Management</h3>
<ul>
<li>Create, update, and manage tickets</li>
<li>Status tracking</li>
<li>Ticket history</li>
</ul>

<h3>💬 Reply Chain System</h3>
<ul>
<li>Chain-based replies</li>
<li>Structured conversations</li>
<li>Thread tracking</li>
</ul>

<h3>🧩 Task Conversion</h3>
<ul>
<li>Select multiple replies</li>
<li>Convert to task</li>
<li>Admin productivity improvement</li>
</ul>

<h3>🔐 Access Control</h3>
<ul>
<li>Admin panel</li>
<li>Customer panel</li>
<li>Role-based access</li>
</ul>

<h3>🎨 Frontend</h3>
<ul>
<li>Vue.js components</li>
<li>Vite build system</li>
<li>TailwindCSS UI</li>
</ul>

<h3>📦 System Design</h3>
<ul>
<li>Modular architecture</li>
<li>API-ready structure</li>
<li>Extendable services</li>
</ul>

<hr/>

<h2>🧠 Design Assumptions</h2>

<ul>
<li>Tickets can grow large over time</li>
<li>Conversation history is critical</li>
<li>Admins require bulk operations</li>
<li>System should be API-ready</li>
<li>Maintainability > quick hacks</li>
<li>Business logic must remain independent</li>
</ul>

<hr/>

<h2>⚙️ Tech Stack</h2>

<ul>
<li>Laravel</li>
<li>Vue.js</li>
<li>Vite</li>
<li>TailwindCSS</li>
<li>MySQL / PostgreSQL</li>
<li>PHPUnit</li>
</ul>

<hr/>

<h2>🧪 Testing</h2>

<pre>
php artisan test
</pre>

<hr/>

<h2>📦 Build Production Assets</h2>

<pre>
npm run build
</pre>

<hr/>

<h2>🔒 Security Notes</h2>

<ul>
<li>Never commit <code>.env</code></li>
<li>Change default credentials</li>
<li>Use HTTPS in production</li>
<li>Set proper file permissions</li>
</ul>

<hr/>

<h2>📈 Future Improvements</h2>

<ul>
<li>WebSocket real-time updates</li>
<li>Advanced filtering & search</li>
<li>Queue & background jobs</li>
<li>Audit logs</li>
<li>REST API</li>
<li>Multi-tenant architecture</li>
</ul>

<hr/>

<h2>📄 License</h2>

<p>
This project is open-source, and the developer's contact information is: ahmadjamshidi19@gmail.com | ahmadjamshidi78@gmail.com.
</p>

