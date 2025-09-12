# LearnSphere

LearnSphere is a modern e-learning platform designed to provide a seamless and interactive learning experience. It allows instructors to create courses and lessons, and students to enroll in them.

## Tech Stack

- **Backend:** PHP, Laravel
- **Frontend:** JavaScript, Livewire, Volt, Blade, Tailwind CSS
- **Database:** MySQL (by default)
- **Development:** Vite
- **PHP Dependencies:** Composer
- **JS Dependencies:** npm

## Prerequisites

Before you begin, ensure you have the following installed on your local machine:

- PHP (>= 8.2)
- Composer
- Node.js & npm
- A database server (e.g., MySQL, PostgreSQL)

## Installation & Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/learnsphere-v1.git
    cd learnsphere-v1/learnsphere
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install JavaScript dependencies:**
    ```bash
    npm install
    ```

4.  **Set up your environment file:**
    - Copy the example environment file:
      ```bash
      cp .env.example .env
      ```
    - Generate a new application key:
      ```bash
      php artisan key:generate
      ```

5.  **Configure your database:**
    - Open the `.env` file and update the `DB_*` variables with your database credentials:
      ```
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=learnsphere
      DB_USERNAME=root
      DB_PASSWORD=
      ```

6.  **Run database migrations and seeders:**
    - This will create the necessary tables and populate the roles table.
      ```bash
      php artisan migrate --seed
      ```

## Running the Application

1.  **Start the Laravel development server:**
    ```bash
    php artisan serve
    ```

2.  **Start the Vite development server for frontend assets:**
    - Open a new terminal for this command.
    ```bash
    npm run dev
    ```

Your application should now be running at `http://127.0.0.1:8000`.

## Running Tests

To run the automated test suite, use the following Artisan command:

```bash
php artisan test
```

## Contributing

Contributions are welcome! Please feel free to submit a pull request.

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
