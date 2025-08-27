
# **Electronics Store API - Laravel 10**

## **Overview**

---

## **Key Features**

---
## **Installation and Setup**

### **1. System Requirements**
- **PHP** 8.2 or later
- **Laravel** 10.x
- **Composer**
- **MySQL** or any  database

---

### **2. Installation Steps**

1. **Clone the Project**
 ```bash
  git clone https://github.com/ABDALRZAQ345/LMS
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
    - Copy the `.env` file:
      ```bash
      cp .env.example .env
      ```
    - Configure database, jwt token , smtp and reverb and firebase .
   
4. **Generate Application Keys**
   ```bash
   php artisan key:generate
   ```
    - generate jwt token
    ```
    php artisan jwt:secret
   ```

5. **Run Migrations and Seed Database**
   ```bash
    php artisan migrate
    php artisan db:seed
   ```

6. **Start the Server**
   ```bash
   php artisan serve
   ```

7. **Run Queues for Background Jobs **
   ```bash
   php artisan queue:work
   ```

---
