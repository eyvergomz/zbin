<div id="top"></div>
<div id="quick-start"></div><div id="features"></div><div id="tech-stack"></div><div id="architecture"></div><div id="configuration"></div><div id="security"></div><div id="storage"></div><div id="i18n"></div><div id="deployment"></div><div id="api"></div><div id="contributing"></div><div id="faq"></div>

<div align="center">

<br/>

<img src="img/icon.svg" alt="zbin logo" width="80" height="80" />

# zbin

**A minimalist, zero-knowledge encrypted pastebin**

<br/>

<a href="https://zbin.onrender.com/" target="_blank"><strong>🔗 Try it live</strong></a>

<br/>

<a href="README.md">English</a> · <a href="README-es.md">Español</a>

<br/>

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)
[![Version](https://img.shields.io/badge/version-1.0.0-brightgreen.svg)](#)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.0%2B-777BB4.svg?logo=php&logoColor=white)](#tech-stack)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E.svg?logo=javascript&logoColor=black)](#tech-stack)
[![AES-256-GCM](https://img.shields.io/badge/encryption-AES--256--GCM-critical.svg)](#security)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.8-7952B3.svg?logo=bootstrap&logoColor=white)](#tech-stack)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED.svg?logo=docker&logoColor=white)](#docker-deployment)
[![i18n](https://img.shields.io/badge/languages-54-orange.svg)](#internationalization)

<br/>

Data is encrypted and decrypted **entirely in the browser** using 256-bit AES in [Galois Counter Mode (GCM)](https://en.wikipedia.org/wiki/Galois/Counter_Mode).<br/>
The server never sees your content, the encryption key, or the password — **ever**.

<br/>

[Quick Start](#quick-start) · [Features](#features) · [Architecture](#architecture) · [Tech Stack](#tech-stack) · [Configuration](#configuration) · [Security](#security) · [Storage](#storage) · [Deployment](#deployment) · [API](#api) · [i18n](#internationalization) · [FAQ](#faq) · [Contributing](#contributing)

</div>

<div align="center">

### Preview

<img src="img/preview-main.png" alt="zbin editor view" width="720" />

<sub>Editor — Create encrypted pastes with expiration, password protection and syntax highlighting</sub>

<br/><br/>

<img src="img/preview-decrypt.png" alt="zbin decrypt view" width="720" />

<sub>Decrypt — Password-protected pastes require client-side decryption</sub>

</div>

---

## 🏷️ Keywords

`zero-knowledge` · `end-to-end-encryption` · `AES-256-GCM` · `pastebin` · `self-hosted` · `privacy` · `open-source` · `PHP` · `JavaScript` · `WebCrypto API` · `Bootstrap 5` · `MVC` · `client-side-encryption` · `burn-after-reading` · `markdown` · `syntax-highlighting` · `file-upload` · `i18n` · `S3` · `Google Cloud Storage` · `Docker`

---

## 🚀 Quick Start

### Prerequisites

| Requirement | Minimum | Recommended |
|------------|---------|-------------|
| **PHP** | 7.4 | 8.2+ |
| **Web Server** | Apache / Nginx / Caddy | Apache with `mod_rewrite` |
| **Browser** | Any modern browser | Chrome, Firefox, Safari, Edge |
| **PHP Extensions** | `zlib` | `zlib`, `pdo`, `mbstring`, `openssl` |

### Option 1: Manual Installation

```sh
# 1. Clone the repository
git clone https://github.com/eyvergomz/zbin.git
cd zbin

# 2. Install PHP dependencies via Composer
composer install --no-dev --optimize-autoloader

# 3. Copy and customize the configuration
cp cfg/conf.sample.php cfg/conf.php

# 4. Create the data directory with restricted permissions
mkdir -p data
chmod 700 data

# 5. Point your web server's document root to the zbin directory
# 6. Open your browser and navigate to your server URL
```

### Option 2: Docker (Recommended)

```sh
# Build and run with Docker
docker build -t zbin .
docker run -d --name zbin -p 8080:80 -v zbin-data:/var/www/html/data zbin

# Open http://localhost:8080 in your browser
```

### Option 3: Docker Compose

```yaml
# docker-compose.yml
version: '3.8'
services:
  zbin:
    build: .
    ports:
      - "8080:80"
    volumes:
      - zbin-data:/var/www/html/data
      - ./cfg/conf.php:/var/www/html/cfg/conf.php:ro
    restart: unless-stopped

volumes:
  zbin-data:
```

```sh
docker compose up -d
```

### Verify Installation

After starting, open `http://localhost:8080` (or your server URL). You should see:
- A text editor area
- A navigation bar with options (New, Expires, Format, etc.)
- The zbin logo in the top-left corner

If you see "Loading..." that never goes away, check the [FAQ](#faq) section.

---

## ✨ Features

### Core

| Feature | Description |
|---------|-------------|
| 🔐 **Zero-Knowledge Encryption** | All encryption/decryption happens client-side using the WebCrypto API. The server stores only encrypted blobs and **never** has access to plaintext content, keys, or passwords. |
| ⏱️ **Flexible Expiration** | Pastes can expire after 5min, 10min, 1 hour, 1 day, 1 week, 1 month, 1 year, or never. Expired pastes are automatically purged. |
| 🔥 **Burn After Reading** | Create self-destructing pastes that are permanently deleted the moment they are viewed once. The server confirms deletion before showing content. |
| 🔑 **Password Protection** | Add an optional password layer on top of the encryption key. Both the URL key and password are required to decrypt. |
| 📎 **File Attachments** | Attach one or multiple files to a paste. Supports drag & drop, clipboard paste (images), and all file types. Files are encrypted alongside the text. |
| 💬 **Discussion System** | Enable threaded discussions/comments on pastes. Commenters get unique identicons based on their IP (configurable: jdenticon, identicon, vizhash, or none). |

### Editor & Display

| Feature | Description |
|---------|-------------|
| 📝 **Markdown Rendering** | Write pastes in Markdown with a live preview tab. Rendered HTML is sanitized through DOMPurify to prevent XSS. |
| 🎨 **Syntax Highlighting** | Automatic syntax highlighting for 100+ programming languages via Google Prettify. Choose from 5 themes: Default, Desert, Doxy, Sons of Obsidian, Sunburst. |
| 📋 **One-Click Copy** | Copy paste content or share link with a single click. Visual feedback confirms the copy action. |
| ↹ **Tab Character Support** | The Tab key inserts a tab character in the editor (instead of moving focus). Toggle with `Ctrl+M` or `Esc`. |

### Sharing & Distribution

| Feature | Description |
|---------|-------------|
| 📱 **QR Code Generation** | Generate a QR code for any paste URL. Scan with a phone to instantly open the paste. |
| ✉️ **Email Sharing** | Share paste links via email with one click. Optionally convert timestamps to UTC for timezone privacy. |
| 🔗 **URL Shortener Integration** | Built-in integration with [YOURLS](https://yourls.org/) and [Shlink](https://shlink.io/) for creating shorter share links. |
| 🗑️ **Delete Links** | Every paste gets a unique deletion link. Share it separately to allow specific people to delete the paste. |

### User Interface

| Feature | Description |
|---------|-------------|
| 🌙 **Dark Mode** | Built-in dark/light theme toggle with smooth CSS transitions. Follows system preference by default. Apple-inspired glass morphism design. |
| 🌍 **54 Languages** | Full internationalization with 54 language translations. Supports RTL languages (Arabic, Hebrew, Farsi, Kurdish). Browser language auto-detection. |
| 🎭 **Template System** | Switch between Bootstrap 5 (modern, default) and Bootstrap 3 (classic) templates. Can be configured per-instance or user-selectable. |
| 📱 **Responsive Design** | Fully responsive layout that works on desktop, tablet, and mobile. Touch-friendly controls. |

### Infrastructure

| Feature | Description |
|---------|-------------|
| 💾 **6 Storage Backends** | Filesystem (default), MySQL, PostgreSQL, SQLite, Google Cloud Storage, AWS S3 / Ceph. Switch backends with a single config change. |
| 🚦 **Rate Limiting** | Configurable per-IP request throttling with subnet exemptions. Prevents abuse without blocking legitimate users. |
| 🔒 **SRI (Subresource Integrity)** | All JavaScript files include SHA-512 integrity hashes. The browser verifies file integrity before execution, preventing tampering. |
| 📦 **CSP (Content Security Policy)** | Strict CSP headers block XSS, clickjacking, and unauthorized resource loading. Configurable for custom deployments. |
| 🌐 **PWA Support** | Web App Manifest enables "Add to Home Screen" on mobile devices. Works as a standalone app. |

---

## 🏗️ Architecture

### How Encryption Works

```mermaid
sequenceDiagram
    participant U as 👤 User
    participant B as 🌐 Browser
    participant S as ☁️ Server

    Note over U,S: === CREATING A PASTE ===

    U->>B: Types text content
    B->>B: Generate random 256-bit key
    B->>B: Compress text with zlib (WASM)
    B->>B: Encrypt with AES-256-GCM
    B->>S: POST encrypted blob (key NOT sent)
    S->>S: Store blob + generate paste ID
    S->>B: Return paste ID
    B->>U: Show URL: server.com/?id=abc#KEY

    Note over U,S: === READING A PASTE ===

    U->>B: Opens URL (key is in #fragment)
    Note right of B: # fragment is NEVER sent to server
    B->>S: GET /?id=abc (no key sent)
    S->>B: Return encrypted blob
    B->>B: Extract key from URL fragment
    B->>B: Decrypt with AES-256-GCM
    B->>B: Decompress with zlib
    B->>U: Display plaintext
```

### Data Flow Overview

```mermaid
flowchart LR
    subgraph Client["🖥️ Browser"]
        direction TB
        A[Text Input] --> B[zlib Compress]
        B --> C[AES-256-GCM Encrypt]
        C --> D[Base58 Encode]
    end

    subgraph Server["☁️ Server"]
        direction TB
        E[PHP Controller] --> F{Storage Backend}
        F --> G[Filesystem]
        F --> H[MySQL/PostgreSQL/SQLite]
        F --> I[Google Cloud Storage]
        F --> J[AWS S3]
    end

    D -->|"HTTPS POST<br/>(encrypted only)"| E
    E -->|"JSON Response<br/>(paste ID)"| D

    style Client fill:#e8f5e9,stroke:#2e7d32,color:#000
    style Server fill:#e3f2fd,stroke:#1565c0,color:#000
```

### MVC Architecture

```mermaid
flowchart TB
    subgraph Entry["Entry Point"]
        A[index.php]
    end

    subgraph Controller["Controller Layer"]
        B[Controller.php] --> C{Request Type?}
        C -->|Create| D[_create]
        C -->|Read| E[_read]
        C -->|Delete| F[_delete]
        C -->|View| G[_view]
    end

    subgraph Model["Model Layer"]
        H[Model.php] --> I[Paste.php]
        H --> J[Comment.php]
        H --> K{Storage}
        K --> L[Filesystem]
        K --> M[Database]
        K --> N[S3Storage]
        K --> O[GoogleCloudStorage]
    end

    subgraph View["View Layer"]
        P[View.php] --> Q[bootstrap5.php]
        P --> R[bootstrap.php]
    end

    subgraph Support["Support Services"]
        S[Configuration.php]
        T[I18n.php]
        U[Filter.php]
        V[TrafficLimiter.php]
        W[ServerSalt.php]
    end

    A --> B
    B --> H
    B --> P
    B --> S
    P --> T

    style Entry fill:#fff3e0,stroke:#e65100,color:#000
    style Controller fill:#e8f5e9,stroke:#2e7d32,color:#000
    style Model fill:#e3f2fd,stroke:#1565c0,color:#000
    style View fill:#f3e5f5,stroke:#7b1fa2,color:#000
    style Support fill:#fce4ec,stroke:#c62828,color:#000
```

> **Key insight:** The encryption key is placed in the URL fragment (`#`), which is **never sent to the server** per the HTTP specification ([RFC 3986 §3.5](https://tools.ietf.org/html/rfc3986#section-3.5)). The server physically cannot access the decryption key.

---

## 🛠️ Tech Stack

### Backend

| Technology | Version | Purpose |
|-----------|---------|---------|
| **PHP** | 7.4+ / 8.0+ | Server-side logic, MVC architecture, PSR-4 autoloading |
| **Composer** | Latest | PHP dependency management and autoloading |
| **jdenticon** | 2.0.0 | Identicon generation for anonymous comment avatars |
| **ip-lib** | 1.22.0 | IP address/subnet parsing for rate limiting & access control |
| **identicon** | 2.0.0 | Alternative identicon style (GitHub-like) |
| **polyfill-php80** | 1.33.0 | PHP 8.0 compatibility for PHP 7.4 environments |

### Frontend

| Technology | Version | Purpose |
|-----------|---------|---------|
| **jQuery** | 3.7.1 | DOM manipulation, AJAX, event handling |
| **Bootstrap** | 5.3.8 | Responsive grid, components, dark mode (CSS variables) |
| **Bootstrap Icons** | SVG sprite | 50+ icons used throughout the UI |
| **Showdown** | 2.1.0 | Markdown → HTML rendering with GFM support |
| **DOMPurify** | 3.3.2 | HTML sanitization to prevent XSS in rendered Markdown |
| **Google Prettify** | Latest | Syntax highlighting for 100+ languages |
| **kjua** | 0.10.0 | QR code generation (canvas-based, no server dependency) |
| **zlib** | 1.3.1 | Compression/decompression via WebAssembly (faster than JS) |
| **base-x** | 5.0.1 | Base58 encoding for URL-safe encryption keys |

### Encryption

| Component | Detail |
|-----------|--------|
| **Algorithm** | AES-256-GCM (Galois Counter Mode) — authenticated encryption with associated data (AEAD) |
| **API** | [WebCrypto API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Crypto_API) — browser-native, hardware-accelerated |
| **Key Size** | 256 bits (randomly generated per paste) |
| **IV** | 96-bit random initialization vector |
| **Auth Tag** | 128-bit authentication tag (ensures data integrity) |
| **Implementation** | 100% client-side — server never sees plaintext or keys |

### Testing

| Technology | Purpose |
|-----------|---------|
| **PHPUnit** | 9.x — PHP unit tests (29 test files) |
| **Mocha** | JS unit tests (15 test modules) |
| **jsdom** | Browser DOM simulation for JS testing |
| **NYC** | Code coverage reporting |

---

## ⚙️ Configuration

All configuration is done via `cfg/conf.php`. Start from the sample:

```sh
cp cfg/conf.sample.php cfg/conf.php
```

### Complete Configuration Reference

#### `[main]` — General Settings

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `name` | string | `"zbin"` | Display name shown in the UI and browser tab |
| `basepath` | string | auto-detected | Full URL with trailing slash (needed for OpenGraph images) |
| `discussion` | bool | `true` | Enable threaded comments on pastes |
| `opendiscussion` | bool | `false` | Pre-check the discussion checkbox |
| `password` | bool | `true` | Enable password protection option |
| `fileupload` | bool | `false` | Enable file attachment uploads |
| `burnafterreadingselected` | bool | `false` | Pre-check burn after reading |
| `defaultformatter` | string | `"plaintext"` | Default format: `plaintext`, `syntaxhighlighting`, `markdown` |
| `syntaxhighlightingtheme` | string | none | Theme: `desert`, `doxy`, `sons-of-obsidian`, `sunburst` |
| `sizelimit` | int | `10000000` | Max paste size in bytes (10 MB) |
| `template` | string | `"bootstrap5"` | UI template: `bootstrap5` or `bootstrap` |
| `templateselection` | bool | `false` | Show template selector in the UI |
| `languageselection` | bool | `false` | Show language picker dropdown |
| `languagedefault` | string | auto-detected | Default language code (e.g., `"en"`, `"es"`) |
| `urlshortener` | string | none | URL shortener API endpoint |
| `qrcode` | bool | `true` | Enable QR code sharing button |
| `email` | bool | `true` | Enable email sharing button |
| `icon` | string | `"jdenticon"` | Comment avatar style: `none`, `identicon`, `jdenticon`, `vizhash` |
| `compression` | string | `"zlib"` | Compression: `"zlib"` or `"none"` |
| `httpwarning` | bool | `true` | Show warning when not using HTTPS |

#### `[expire]` — Expiration Settings

```ini
[expire]
default = "1week"           ; Default expiration selection

[expire_options]
5min = 300                  ; 5 minutes
10min = 600                 ; 10 minutes
1hour = 3600                ; 1 hour
1day = 86400                ; 1 day
1week = 604800              ; 1 week (default)
1month = 2592000            ; 30 days
1year = 31536000            ; 365 days
never = 0                   ; Never expires
```

#### `[traffic]` — Rate Limiting

```ini
[traffic]
limit = 10                  ; Seconds between requests per IP (0 = disabled)
; exempted = "10.0.0/8"     ; Exempt subnets (comma-separated)
; creators = "10.0.0/8"     ; Restrict paste creation to these IPs
; header = "X_FORWARDED_FOR" ; IP header when behind a reverse proxy
```

#### `[purge]` — Auto-Cleanup

```ini
[purge]
limit = 300                 ; Seconds between purge runs (0 = every request)
batchsize = 10              ; Max expired pastes to delete per run (0 = disabled)
```

#### `[formatter_options]` — Format Labels

```ini
[formatter_options]
plaintext = "Plain Text"
syntaxhighlighting = "Source Code"
markdown = "Markdown"
```

---

## 🔒 Security

### Zero-Knowledge Architecture

zbin implements a **zero-knowledge** design where the server is mathematically unable to access paste content:

```
┌─────────────────────────────────────────────────────────┐
│                    URL Structure                         │
│                                                         │
│  https://zbin.example.com/?abc123#CryptKeyGoesHere     │
│  ├──────────── sent to server ──────┤├── NEVER sent ──┤│
│                                                         │
│  The # fragment is stripped by the browser before the   │
│  HTTP request is made. The server has no mechanism to   │
│  access it.                                             │
└─────────────────────────────────────────────────────────┘
```

### Security Layers

| Layer | Mechanism | Purpose |
|-------|-----------|---------|
| **Encryption** | AES-256-GCM | Confidentiality + integrity of paste data |
| **Key isolation** | URL fragment (`#`) | Server never receives the decryption key |
| **Password** | Optional second factor | Additional protection even if URL is leaked |
| **SRI** | SHA-512 hashes | Ensures JS files haven't been tampered with |
| **CSP** | Content Security Policy | Blocks XSS, clickjacking, unauthorized resources |
| **Sanitization** | DOMPurify | Strips malicious HTML from Markdown output |
| **Rate limiting** | Per-IP throttling | Prevents brute-force and abuse |
| **HTTPS detection** | Warning banner | Alerts users to insecure connections |
| **Bot detection** | User-agent filtering | Prevents bots from triggering burn-after-reading |

### Why AES-256-GCM?

| Property | Benefit |
|----------|---------|
| **256-bit key** | Computationally infeasible to brute-force (2²⁵⁶ combinations) |
| **GCM mode** | Authenticated encryption — detects if ciphertext was modified |
| **Hardware acceleration** | WebCrypto uses CPU AES-NI instructions when available |
| **NIST approved** | Used by governments and military worldwide |

### Content Security Policy

Default CSP (the most restrictive):

```
default-src 'none';
base-uri 'self';
form-action 'none';
manifest-src 'self';
connect-src * blob:;
script-src 'self' 'wasm-unsafe-eval';
style-src 'self';
font-src 'self';
frame-ancestors 'none';
frame-src blob:;
img-src 'self' data: blob:;
media-src blob:;
object-src blob:;
sandbox allow-same-origin allow-scripts allow-forms allow-modals allow-downloads
```

### Threat Model

| Threat | Mitigation |
|--------|-----------|
| Server compromise | Data is encrypted; attacker gets only ciphertext |
| Man-in-the-middle | HTTPS + SRI + CSP prevent injection |
| XSS attack | DOMPurify sanitization + strict CSP |
| Brute-force | Rate limiting + 256-bit key space |
| URL leakage (shared link) | Optional password adds a second factor |
| Admin snooping | Zero-knowledge — admin cannot decrypt |

---

## 💾 Storage Backends

### Filesystem (Default)

The simplest option — stores each paste as a file in the `data/` directory.

```ini
[model]
class = Filesystem
[model_options]
dir = PATH "data"
```

**Pros:** No dependencies, easy backup (just copy the directory)
**Cons:** Slower with millions of pastes, no built-in replication

### MySQL / MariaDB

```ini
[model]
class = Database
[model_options]
dsn = "mysql:host=localhost;dbname=zbin;charset=UTF8"
tbl = "zbin_"
usr = "zbin_user"
pwd = "your_secure_password"
opt[12] = true    ; PDO::ATTR_PERSISTENT
```

### PostgreSQL

```ini
[model]
class = Database
[model_options]
dsn = "pgsql:host=localhost;dbname=zbin"
tbl = "zbin_"
usr = "zbin_user"
pwd = "your_secure_password"
opt[12] = true
```

### SQLite

Perfect for small deployments — no external database needed.

```ini
[model]
class = Database
[model_options]
dsn = "sqlite:" PATH "data/db.sq3"
usr = null
pwd = null
opt[12] = true
```

### Google Cloud Storage

```ini
[model]
class = GoogleCloudStorage
[model_options]
bucket = "my-zbin-bucket"
prefix = "pastes"
uniformacl = false
```

### AWS S3 / Ceph

```ini
[model]
class = S3Storage
[model_options]
region = "us-east-1"
version = "latest"
bucket = "my-zbin-bucket"
accesskey = "AKIAIOSFODNN7EXAMPLE"
secretkey = "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
```

For Ceph/MinIO (S3-compatible):

```ini
[model]
class = S3Storage
[model_options]
region = ""
version = "2006-03-01"
endpoint = "https://s3.my-ceph.example.com"
use_path_style_endpoint = true
bucket = "my-bucket"
accesskey = "my-rados-user"
secretkey = "my-rados-pass"
```

### Database Schema

When using the Database backend, tables are created automatically. The schema:

```sql
-- Pastes table
CREATE TABLE zbin_paste (
    dataid CHAR(16) NOT NULL,
    data MEDIUMBLOB NOT NULL,
    postdate INT NOT NULL,
    expiredate INT NOT NULL,
    opendiscussion INT NOT NULL,
    burnafterreading INT NOT NULL,
    meta TEXT NOT NULL,
    attachment MEDIUMBLOB,
    attachmentname BLOB,
    PRIMARY KEY (dataid)
);

-- Comments table
CREATE TABLE zbin_comment (
    dataid CHAR(16) NOT NULL,
    pasteid CHAR(16) NOT NULL,
    parentid CHAR(16) NOT NULL,
    data BLOB NOT NULL,
    nickname BLOB,
    vizhash BLOB,
    postdate INT NOT NULL,
    PRIMARY KEY (dataid)
);

-- Configuration table
CREATE TABLE zbin_config (
    id CHAR(16) NOT NULL,
    value TEXT NOT NULL,
    PRIMARY KEY (id)
);
```

---

## 🚢 Deployment

### Docker Deployment

The project includes a production-ready `Dockerfile`:

```sh
# Build
docker build -t zbin .

# Run with persistent data
docker run -d \
  --name zbin \
  -p 8080:80 \
  -v zbin-data:/var/www/html/data \
  -v $(pwd)/cfg/conf.php:/var/www/html/cfg/conf.php:ro \
  --restart unless-stopped \
  zbin
```

### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name zbin.example.com;

    root /var/www/zbin;
    index index.php;

    # SSL
    ssl_certificate     /etc/letsencrypt/live/zbin.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/zbin.example.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer" always;
    add_header Strict-Transport-Security "max-age=63072000" always;

    # Block access to sensitive directories
    location ~ ^/(cfg|lib|tst|vendor) {
        deny all;
        return 403;
    }

    # Block access to hidden files
    location ~ /\. {
        deny all;
        return 403;
    }

    # PHP processing
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # URL rewriting
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
}
```

### Apache Configuration

The included `.htaccess` handles URL rewriting automatically. Ensure `mod_rewrite` is enabled:

```sh
a2enmod rewrite
systemctl restart apache2
```

### Railway / Render / Fly.io

Since the project includes a `Dockerfile`, it deploys directly to container platforms:

1. Connect your GitHub repository
2. Select the `main` branch
3. The platform auto-detects the Dockerfile
4. Set port to `80`
5. Deploy

---

## 📡 API

zbin exposes a simple JSON API for programmatic paste management.

### Create a Paste

```http
POST / HTTP/1.1
Content-Type: application/json
X-Requested-With: JSONHttpRequest

{
  "v": 2,
  "adata": [[base64_iv, base64_salt, iterations, keysize, tagsize, algo, mode, compression], "plaintext", discussion, burnafterreading],
  "ct": "base64_encrypted_ciphertext",
  "meta": {
    "expire": "1week"
  }
}
```

**Response (success):**

```json
{
  "status": 0,
  "id": "abc123",
  "url": "/?abc123",
  "deletetoken": "def456..."
}
```

### Read a Paste

```http
GET /?abc123 HTTP/1.1
Accept: application/json
X-Requested-With: JSONHttpRequest
```

**Response:**

```json
{
  "status": 0,
  "id": "abc123",
  "v": 2,
  "adata": [...],
  "ct": "base64_encrypted_ciphertext",
  "meta": {
    "created": 1709234567,
    "time_to_live": 604800
  },
  "comment_count": 0,
  "comment_offset": 0,
  "comments": []
}
```

### Delete a Paste

```http
DELETE /?abc123 HTTP/1.1
Content-Type: application/json
X-Requested-With: JSONHttpRequest

{
  "pasteid": "abc123",
  "deletetoken": "def456..."
}
```

**Response:**

```json
{
  "status": 0,
  "id": "abc123"
}
```

### Error Responses

```json
{
  "status": 1,
  "message": "Error description here"
}
```

| Status | Meaning |
|--------|---------|
| `0` | Success |
| `1` | Error (see `message` field) |

---

## 🌍 Internationalization

zbin supports **54 languages** with automatic browser detection:

| Language | Code | | Language | Code | | Language | Code |
|----------|------|-|----------|------|-|----------|------|
| Arabic 🇸🇦 | `ar` | | German 🇩🇪 | `de` | | Polish 🇵🇱 | `pl` |
| Bulgarian 🇧🇬 | `bg` | | Greek 🇬🇷 | `el` | | Portuguese 🇵🇹 | `pt` |
| Catalan | `ca` | | Hebrew 🇮🇱 | `he` | | Romanian 🇷🇴 | `ro` |
| Chinese 🇨🇳 | `zh` | | Hindi 🇮🇳 | `hi` | | Russian 🇷🇺 | `ru` |
| Corsican | `co` | | Hungarian 🇭🇺 | `hu` | | Slovak 🇸🇰 | `sk` |
| Czech 🇨🇿 | `cs` | | Indonesian 🇮🇩 | `id` | | Slovenian 🇸🇮 | `sl` |
| English 🇺🇸 | `en` | | Italian 🇮🇹 | `it` | | Spanish 🇪🇸 | `es` |
| Estonian 🇪🇪 | `et` | | Japanese 🇯🇵 | `ja` | | Swedish 🇸🇪 | `sv` |
| Farsi 🇮🇷 | `fa` | | Korean 🇰🇷 | `ko` | | Thai 🇹🇭 | `th` |
| Finnish 🇫🇮 | `fi` | | Kurdish | `ku` | | Turkish 🇹🇷 | `tr` |
| French 🇫🇷 | `fr` | | Norwegian 🇳🇴 | `no` | | Ukrainian 🇺🇦 | `uk` |

**RTL support:** Arabic (`ar`), Hebrew (`he`), Farsi (`fa`), Kurdish (`ku`)

### Configuration

```ini
; Set a fixed language (disables auto-detection)
languagedefault = "es"

; Or enable the language selector dropdown
languageselection = true
```

### Adding a New Language

1. Copy `i18n/en.json` to `i18n/xx.json` (your language code)
2. Translate all string values
3. Add entry to `i18n/languages.json`

---

## 📁 Project Structure

```
zbin/
├── cfg/                          # Configuration
│   ├── .htaccess                 # Block direct access to config
│   └── conf.sample.php           # Sample configuration (copy to conf.php)
│
├── css/                          # Stylesheets
│   ├── bootstrap5/               # Bootstrap 5 theme
│   │   ├── bootstrap-5.3.8.css   # Bootstrap framework
│   │   ├── bootstrap.rtl-5.3.8.css  # RTL variant
│   │   └── zbin.css              # Custom Apple-inspired styles
│   ├── bootstrap/                # Bootstrap 3 legacy theme
│   │   ├── bootstrap-3.4.1.css
│   │   ├── darkstrap-0.9.3.css   # Dark theme
│   │   ├── zbin.css
│   │   └── fonts/                # Glyphicon fonts
│   ├── prettify/                 # Syntax highlighting themes (5)
│   ├── common.css                # Shared styles (all templates)
│   └── noscript.css              # Fallback for JavaScript-disabled browsers
│
├── i18n/                         # 54 language translation files (JSON)
│
├── img/                          # Icons and images
│   ├── icon.svg                  # App icon (hexagon keyhole)
│   ├── logo.svg                  # Full logo (icon + text)
│   ├── bootstrap-icons.svg       # Icon sprite (50+ icons)
│   ├── favicon.ico               # Browser favicon
│   ├── apple-touch-icon.png      # iOS home screen icon
│   ├── android-chrome-*.png      # Android PWA icons
│   └── mstile-*.png              # Windows tile icons
│
├── js/                           # Frontend JavaScript
│   ├── zbin.js                   # Main application (6000+ lines, 20 modules)
│   ├── common.js                 # Shared test utilities
│   ├── legacy.js                 # Browser compatibility checks
│   ├── jquery-3.7.1.js           # jQuery library
│   ├── bootstrap-5.3.8.js        # Bootstrap JS components
│   ├── showdown-2.1.0.js         # Markdown renderer
│   ├── purify-3.3.2.js           # HTML sanitizer (XSS protection)
│   ├── prettify.js               # Syntax highlighter
│   ├── kjua-0.10.0.js            # QR code generator
│   ├── zlib-1.3.1-2.js           # Compression (JS loader)
│   ├── zlib-1.3.1.wasm           # Compression (WebAssembly binary)
│   ├── base-x-5.0.1.js           # Base encoding for keys
│   ├── dark-mode-switch.js       # Theme toggle logic
│   └── test/                     # 15 Mocha test modules
│
├── lib/                          # PHP Backend (Zbin namespace, PSR-4)
│   ├── Controller.php            # Main MVC controller (request routing)
│   ├── Configuration.php         # Config file parsing and validation
│   ├── Model.php                 # Data model factory (storage abstraction)
│   ├── View.php                  # Template rendering engine
│   ├── Request.php               # HTTP request parsing
│   ├── Filter.php                # Input/output formatting
│   ├── I18n.php                  # Internationalization (54 languages)
│   ├── Json.php                  # JSON utilities
│   ├── FormatV2.php              # Data format conversion
│   ├── TemplateSwitcher.php      # Template selection logic
│   ├── Vizhash16x16.php          # Visual hash (IP-based avatars)
│   ├── Data/                     # Storage backends
│   │   ├── AbstractData.php      # Storage interface
│   │   ├── Filesystem.php        # File-based storage
│   │   ├── Database.php          # PDO database storage
│   │   ├── GoogleCloudStorage.php
│   │   └── S3Storage.php         # AWS S3 / Ceph
│   ├── Model/                    # Data models
│   │   ├── AbstractModel.php     # Base model
│   │   ├── Paste.php             # Paste CRUD + validation
│   │   └── Comment.php           # Comment CRUD + validation
│   ├── Persistence/              # Server-side state
│   │   ├── AbstractPersistence.php
│   │   ├── ServerSalt.php        # HMAC salt for delete tokens
│   │   ├── TrafficLimiter.php    # Rate limiting per IP
│   │   └── PurgeLimiter.php      # Expired paste cleanup throttle
│   ├── Proxy/                    # URL shortener proxies
│   │   ├── AbstractProxy.php
│   │   ├── YourlsProxy.php       # YOURLS integration
│   │   └── ShlinkProxy.php       # Shlink integration
│   └── Exception/                # Custom exceptions
│       ├── JsonException.php
│       └── TranslatedException.php
│
├── tpl/                          # HTML templates (PHP)
│   ├── bootstrap5.php            # Modern template (default)
│   ├── bootstrap.php             # Classic Bootstrap 3 template
│   └── shortenerproxy.php        # URL shortener proxy page
│
├── tst/                          # PHP unit tests (29 files)
│   ├── phpunit.xml               # PHPUnit configuration
│   └── *.php                     # Test classes
│
├── index.php                     # Application entry point
├── Dockerfile                    # Docker container definition
├── .htaccess                     # Apache URL rewriting
├── composer.json                 # PHP dependencies
├── manifest.json                 # PWA manifest
├── browserconfig.xml             # Windows tile config
├── robots.txt                    # Search engine directives
├── Makefile                      # Build automation
├── CHANGELOG.md                  # Version history
└── LICENSE.md                    # MIT License
```

---

## ❓ FAQ

### "Loading..." never disappears

This usually means JavaScript failed to load. Common causes:

1. **SRI hash mismatch** — If you modified any `.js` file, you must regenerate its SHA-512 hash in `lib/Configuration.php`. Use:
   ```sh
   openssl dgst -sha512 -binary js/zbin.js | openssl base64 -A
   ```
2. **Mixed content** — Serving the site over HTTP but resources reference HTTPS (or vice versa)
3. **CSP blocking** — Custom CSP headers may block script execution. Check browser console.
4. **Reverse proxy** — Ensure your proxy forwards all request headers correctly

### Can the server admin read my pastes?

**No.** The encryption key exists only in the URL fragment (`#`), which the browser never sends to the server. The admin can see encrypted blobs but cannot decrypt them without the key.

### What happens if I lose the URL?

The paste is **irrecoverable**. There is no "forgot password" or key recovery. This is by design — if there were a recovery mechanism, it would mean the server has access to the keys, breaking zero-knowledge.

### How does burn-after-reading work?

1. The paste is marked as "burn after reading" on the server
2. When someone opens the URL, the browser first shows a confirmation dialog
3. Only after clicking "Yes, see it" does the server delete the paste and return the data
4. The paste is permanently gone — even the original URL will return a 404

### Is zbin suitable for sensitive data?

zbin provides strong encryption (AES-256-GCM) and a zero-knowledge architecture. However:
- The URL contains the decryption key — anyone with the URL can read the paste
- For maximum security, also set a strong password
- Use HTTPS to prevent URL interception
- Consider "burn after reading" for one-time secrets

---

## 🤝 Contributing

Contributions are welcome! Here's how to get started:

### Development Setup

```sh
# Clone the repository
git clone https://github.com/eyvergomz/zbin.git
cd zbin

# Install PHP dev dependencies
composer install

# Install JS dev dependencies
cd js && npm install && cd ..

# Run PHP tests
cd tst && phpunit && cd ..

# Run JS tests
cd js && npx mocha && cd ..
```

### Code Style

- **PHP:** PSR-12 coding standard, PSR-4 autoloading under `Zbin\` namespace
- **JavaScript:** ESLint configured, jQuery module pattern
- **CSS:** BEM-inspired naming with `--zb-` prefix for custom properties

### Pull Request Guidelines

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Make your changes
4. Run tests (`make test`)
5. If you modified any JS files, regenerate SRI hashes
6. Commit with a clear message
7. Push and open a Pull Request

---

## 📄 License

Licensed under the **[MIT License](LICENSE.md)**.

Copyright (c) 2026 [eyvergomz](https://github.com/eyvergomz)

See [LICENSE.md](LICENSE.md) for the full license text and third-party library attributions.

<div align="center">
<br/>

---

<sub>Built with 🔒 by <a href="https://github.com/eyvergomz">eyvergomz</a></sub>

</div>
