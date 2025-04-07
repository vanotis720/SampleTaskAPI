# TaskAPI

This guide will help you set up and launch this Laravel 12 API.

## Prerequisites

Ensure you have the following installed on your system:

-   PHP >= 8.1
-   Composer

## Installation Steps

1. **Clone the Repository**

    ```bash
    git clone https://github.com/vanotis720/SampleTaskAPI.git
    cd SampleTaskAPI
    ```

2. **Install Dependencies**

    ```bash
    composer install
    ```

3. **Set Up Environment**

    - Copy the `.env.example` file to `.env`:
        ```bash
        cp .env.example .env
        ```
    - Update the `.env` file with your database credentials and other configurations if needed.

4. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

5. **Run Migrations**
    ```bash
    php artisan migrate
    ```

## Running the Application

1. **Start the Development Server**

    ```bash
    php artisan serve
    ```

2. **Access the Application**
   Open your browser and navigate to `http://127.0.0.1:8000`.

## Additional Commands

-   **Clear Cache**
    ```bash
    php artisan cache:clear
    ```

For more details, refer to the [Laravel 12 documentation](https://laravel.com/docs/12.x).

<!-- copyright -->

## License

This project is licensed under the MIT License. See the [LICENSE](https://opensource.org/license/mit) file for details.

## Author

-   [Contact](https://bento.me/vancodes)
-   [GitHub](https://github.com/vanotis720)
-   [Youtube](https://www.youtube.com/channel/UCMFgTZbS8cQHfbgtX5Y3Vfw)
