# Laravel Passport Auth API

Repo luyện tập API xác thực bằng Laravel Passport: đăng ký tài khoản, nhận access token, đăng xuất và refresh token.

> **Lưu ý:** repo này bật OAuth2 Password Grant để người mới dễ nhìn thấy luồng email/mật khẩu → access token → refresh token. Laravel không khuyến nghị grant này cho hệ thống thật. Khi áp dụng cho sản phẩm, hãy chọn OAuth Authorization Code + PKCE (hoặc Sanctum nếu chỉ cần token đơn giản).

## Yêu cầu

- PHP 8.3 trở lên, bật extension `sodium`.
- Composer.
- Laravel 13 và Laravel Passport 13 (đã khai báo trong `composer.json` / `composer.lock`).

## Cài và chạy

Mở terminal trong thư mục repo:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan passport:install
php artisan passport:client --password
php artisan serve
```

Nếu file SQLite chưa tồn tại, tạo trước khi chạy migration:

```powershell
New-Item -ItemType File database/database.sqlite
```

Trong `passport:install`, xác nhận chạy migration. Lệnh này tạo khóa mã hóa và publish migration/config Passport. Nếu `php -m` chưa có `sodium`, bật extension `sodium` trong `php.ini` rồi mở terminal mới.

Lệnh tạo password client sẽ in ra `client_id` và `client_secret`. Lưu chúng trong `.env` cục bộ, không commit secret lên Git:

```dotenv
PASSPORT_CLIENT_ID=...
PASSPORT_CLIENT_SECRET=...
```

Nếu muốn dùng SQLite, tạo file `database/database.sqlite` trước khi migrate và cấu hình `DB_CONNECTION=sqlite` trong `.env`.

## API

### 1. Tạo tài khoản

`POST /api/auth/register` với JSON:

```json
{
  "name": "Nguyen Van A",
  "email": "a@example.com",
  "password": "secret1234",
  "password_confirmation": "secret1234"
}
```

Controller kiểm tra dữ liệu, rồi tạo user. Model `User` tự băm password nhờ cast `hashed`.

### 2. Đăng nhập và nhận token

Passport đã đăng ký endpoint OAuth `POST /oauth/token`. Gửi `application/x-www-form-urlencoded`:

```text
grant_type=password
client_id=<PASSPORT_CLIENT_ID>
client_secret=<PASSPORT_CLIENT_SECRET>
username=a@example.com
password=secret1234
scope=
```

Phản hồi có `access_token`, `refresh_token` và `expires_in`. Gửi access token trong header:

```text
Authorization: Bearer <access_token>
```

### 3. Xem user hiện tại

`GET /api/me` cần Bearer token hợp lệ.

### 4. Đăng xuất

`POST /api/auth/logout` cần Bearer token. API thu hồi access token hiện tại cùng refresh token liên quan.

### 5. Refresh access token

Gửi form đến `POST /oauth/token`:

```text
grant_type=refresh_token
refresh_token=<refresh_token>
client_id=<PASSPORT_CLIENT_ID>
client_secret=<PASSPORT_CLIENT_SECRET>
scope=
```

Passport trả về bộ token mới. Dùng refresh token mới mà phản hồi trả về cho lần tiếp theo.

## Đọc code theo luồng

1. `routes/api.php`: URL gọi controller nào; các URL trong nhóm `auth:api` cần token.
2. `app/Http/Controllers/Api/AuthController.php`: validate đăng ký, trả user, thu hồi token khi logout.
3. `app/Models/User.php`: `HasApiTokens` nối user với token Passport; `OAuthenticatable` cho Passport biết User là model đăng nhập được.
4. `config/auth.php`: guard `api` dùng driver `passport` để xác minh Bearer token.
5. `app/Providers/AppServiceProvider.php`: bật Password Grant cho mục đích học tập.
6. `/oauth/token`: endpoint do Passport cung cấp; `grant_type` quyết định đây là đăng nhập hay refresh.

## Không commit

Không commit `.env`, `storage/oauth-private.key`, `storage/oauth-public.key`, `client_secret`, hay token nhận được từ API. `.gitignore` đã loại trừ `.env` và file khóa trong `storage`.
