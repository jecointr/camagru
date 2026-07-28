# Camagru

*This project has been created as part of the 42 curriculum by jecointr.*

A small Instagram-like web app: capture or upload a photo, overlay a sticker server-side, then publish it to a public gallery where users can like and comment.

---

## Description

Camagru is a PHP web application that lets authenticated users:

- take a picture with their webcam **or** upload an image
- select a superposable sticker (PNG with alpha channel)
- generate the final montage **on the server**
- browse a public gallery of all montages
- like and comment (logged-in users only)

Account creation requires email confirmation. Password reset and profile editing (username, email, password, avatar, comment-notification preference) are supported.

The UI is responsive (header / main / footer) and works on mobile widths.

---

## Features

### Mandatory (subject)

| Area | What is implemented |
|------|---------------------|
| **Auth** | Sign up (email + username + password complexity), email verification link, login, logout, password reset by email |
| **Profile** | Update username, email, password; toggle email notifications for comments (on by default) |
| **Gallery** | Public list ordered by date, pagination (≥ 5 per page), likes & comments for logged-in users, email notify on comment |
| **Editor** | Webcam preview + sticker list + capture; capture disabled until a sticker is selected; side thumbnails of previous shots; image upload fallback; delete own images only; server-side merge |
| **Security** | No plain-text passwords, prepared SQL, XSS escaping, CSRF tokens, upload checks, credentials in `.env` (gitignored) |
| **Deploy** | Docker / Compose — one command to start the stack |
| **Client** | HTML, CSS, vanilla JavaScript (browser APIs only) |
| **Server** | PHP with standard-library equivalents (PDO, GD, sessions, `mail`, etc.) |

### Extra / bonus-style

- AJAX likes, comments, and infinite gallery scroll
- Live sticker preview on the webcam (draggable overlay); final compose still done server-side
- Social share links (Twitter / Facebook / WhatsApp)
- Profile picture upload
- Light / dark theme toggle

---

## Tech stack

| Layer | Choice | Why |
|-------|--------|-----|
| Backend | PHP 8.1 | Allowed by the subject; no framework beyond PHP stdlib-equivalent usage |
| Frontend | HTML / CSS / JS | Native browser APIs only (`getUserMedia`, `fetch`, Canvas, etc.) |
| Database | MariaDB 11.8 | Relational schema with foreign keys; matches the project’s user / image / like / comment model |
| Web server | Apache (PHP image) + `mod_rewrite` | Front controller in `public/index.php` |
| Images | PHP GD | Server-side overlay of sticker on photo |
| Mail (dev) | MailHog | Catch verification / reset / comment emails locally |
| DB UI (dev) | Adminer | Optional inspection of the database |
| Config | `.env` | Secrets never committed |

Architecture follows a simple **MVC-style** layout: `controllers/`, `models/`, `views/`, with `public/` as the document root.

---

## Project structure

```
camagru/
├── config/           # Database singleton, schema SQL, setup script
├── controllers/      # Auth, Gallery, Editor
├── models/           # User, Gallery, ImageProcessor
├── views/            # Pages + layout + partials
├── public/           # Document root (index.php, css, js, uploads, filters)
├── docker-compose.yml
├── Dockerfile
├── .env              # Local secrets (not committed)
└── README.md
```

---

## Database schema

```
users
  id, username, email, password (bcrypt), token, is_verified,
  notification_active, reset_token, reset_expires, profile_pic, created_at

images
  id, user_id → users, filename, created_at

likes
  id, user_id → users, image_id → images
  UNIQUE(user_id, image_id)

comments
  id, user_id → users, image_id → images, comment, created_at
```

Schema is applied automatically on first DB init via `config/setup.sql` mounted into MariaDB.

---

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) and Docker Compose
- A modern browser: **Firefox (≥ 41)** and/or **Chrome (≥ 46)** (subject requirement)
- Webcam optional — image upload is supported if none is available

---

## Installation & run

1. **Clone the repository** and enter the project directory.

2. **Create your environment file** (never commit real secrets):

```bash
cp .env.example .env   # or create .env manually — see below
```

Example `.env`:

```env
DB_HOST=db
DB_NAME=camagru
DB_USER=camagru_user
DB_PASSWORD=user_password
DB_ROOT_PASSWORD=rootpassword

APP_ENV=development
APP_URL=http://localhost:8080
```

3. **Start the stack** (one command):

```bash
docker compose up --build
```

4. Open the app:

| Service | URL |
|---------|-----|
| Camagru | http://localhost:8080 |
| MailHog (emails) | http://localhost:8025 |
| Adminer (DB) | http://localhost:8081 |

5. **Register** a user, confirm the account via the link in MailHog, then log in and use the editor / gallery.

To stop:

```bash
docker compose down
```

---

## Security notes

Aligned with the subject (passwords, XSS, SQLi, unwanted uploads, CSRF):

- Passwords hashed with `password_hash()` / `PASSWORD_BCRYPT`
- PDO prepared statements (`ATTR_EMULATE_PREPARES` disabled)
- Output escaped with `htmlspecialchars`
- CSRF token on forms and AJAX mutations
- Avatar uploads: MIME check via `finfo`, size limit, re-encoded with GD
- Montage images rebuilt server-side from validated image data + known filter files
- Session ID regenerated on login
- Directory listing disabled under `public/` (`.htaccess`)
- Credentials loaded from `.env` (listed in `.gitignore`)

The subject allows `getUserMedia()`-related console noise when HTTPS is unavailable; local HTTP on `localhost` is expected in development.

---

## Subject constraints (summary)

From `en.subject.pdf` (Camagru v4.1):

- Server: any language limited to PHP standard-library equivalents  
- Client: HTML, CSS, JavaScript with native browser APIs  
- CSS frameworks allowed if they do not pull in forbidden JavaScript  
- No server/client frameworks or libraries outside those rules  
- Containerized deploy with a single command  
- Secure forms and site; credentials only in local `.env`  
- Compatible with Firefox and Chrome as specified  

---

## Resources

- [PHP manual](https://www.php.net/manual/en/)
- [PDO](https://www.php.net/manual/en/book.pdo.php)
- [GD image processing](https://www.php.net/manual/en/book.image.php)
- [MediaDevices.getUserMedia()](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/) (XSS, SQLi, CSRF, file upload)
- [Docker Compose](https://docs.docker.com/compose/)
- Project subject: `en.subject.pdf`

---

## License

School project for educational purposes (42).
