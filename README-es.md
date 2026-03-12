<div id="top"></div>
<div id="quick-start"></div><div id="features"></div><div id="tech-stack"></div><div id="architecture"></div><div id="configuration"></div><div id="security"></div><div id="storage"></div><div id="i18n"></div><div id="deployment"></div><div id="api"></div><div id="contributing"></div><div id="faq"></div>

<div align="center">

<br/>

<img src="img/icon.svg" alt="zbin logo" width="80" height="80" />

# zbin

**Un pastebin minimalista con cifrado de conocimiento cero**

<br/>

<a href="https://zbin.onrender.com/" target="_blank"><strong>🔗 Pruébalo en vivo</strong></a>

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

Los datos se cifran y descifran **completamente en el navegador** usando AES de 256 bits en [modo Galois/Counter (GCM)](https://en.wikipedia.org/wiki/Galois/Counter_Mode).<br/>
El servidor nunca ve tu contenido, la clave de cifrado ni la contraseña — **jamás**.

<br/>

[Inicio Rápido](#quick-start) · [Características](#features) · [Arquitectura](#architecture) · [Stack Tecnológico](#tech-stack) · [Configuración](#configuration) · [Seguridad](#security) · [Almacenamiento](#storage) · [Despliegue](#deployment) · [API](#api) · [i18n](#internationalization) · [FAQ](#faq) · [Contribuir](#contributing)

</div>

<div align="center">

### Vista Previa

<img src="img/preview-main.png" alt="zbin vista del editor" width="720" />

<sub>Editor — Crea pastes cifrados con expiración, contraseña y resaltado de sintaxis</sub>

<br/><br/>

<img src="img/preview-decrypt.png" alt="zbin vista de descifrado" width="720" />

<sub>Descifrar — Los pastes protegidos requieren descifrado del lado del cliente</sub>

</div>

---

## 🏷️ Palabras Clave

`zero-knowledge` · `end-to-end-encryption` · `AES-256-GCM` · `pastebin` · `self-hosted` · `privacy` · `open-source` · `PHP` · `JavaScript` · `WebCrypto API` · `Bootstrap 5` · `MVC` · `client-side-encryption` · `burn-after-reading` · `markdown` · `syntax-highlighting` · `file-upload` · `i18n` · `S3` · `Google Cloud Storage` · `Docker`

---

## 🚀 Inicio Rápido

### Requisitos Previos

| Requisito | Mínimo | Recomendado |
|------------|---------|-------------|
| **PHP** | 7.4 | 8.2+ |
| **Servidor Web** | Apache / Nginx / Caddy | Apache con `mod_rewrite` |
| **Navegador** | Cualquier navegador moderno | Chrome, Firefox, Safari, Edge |
| **Extensiones PHP** | `zlib` | `zlib`, `pdo`, `mbstring`, `openssl` |

### Opción 1: Instalación Manual

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

### Opción 2: Docker (Recomendado)

```sh
# Build and run with Docker
docker build -t zbin .
docker run -d --name zbin -p 8080:80 -v zbin-data:/var/www/html/data zbin

# Open http://localhost:8080 in your browser
```

### Opción 3: Docker Compose

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

### Verificar la Instalación

Después de iniciar, abre `http://localhost:8080` (o la URL de tu servidor). Deberías ver:
- Un área de editor de texto
- Una barra de navegación con opciones (Nuevo, Expiración, Formato, etc.)
- El logo de zbin en la esquina superior izquierda

Si ves "Cargando..." y nunca desaparece, consulta la sección de [FAQ](#faq).

---

## ✨ Características

### Principales

| Característica | Descripción |
|---------|-------------|
| 🔐 **Cifrado de Conocimiento Cero** | Todo el cifrado/descifrado ocurre en el lado del cliente usando la WebCrypto API. El servidor solo almacena blobs cifrados y **nunca** tiene acceso al contenido en texto plano, claves o contraseñas. |
| ⏱️ **Expiración Flexible** | Los pastes pueden expirar después de 5min, 10min, 1 hora, 1 día, 1 semana, 1 mes, 1 año, o nunca. Los pastes expirados se eliminan automáticamente. |
| 🔥 **Destruir Después de Leer** | Crea pastes autodestructivos que se eliminan permanentemente en el momento en que se visualizan una vez. El servidor confirma la eliminación antes de mostrar el contenido. |
| 🔑 **Protección con Contraseña** | Añade una capa opcional de contraseña sobre la clave de cifrado. Tanto la clave de la URL como la contraseña son necesarias para descifrar. |
| 📎 **Archivos Adjuntos** | Adjunta uno o múltiples archivos a un paste. Soporta arrastrar y soltar, pegado desde el portapapeles (imágenes) y todos los tipos de archivos. Los archivos se cifran junto con el texto. |
| 💬 **Sistema de Discusión** | Habilita discusiones/comentarios con hilos en los pastes. Los comentaristas obtienen identicons únicos basados en su IP (configurable: jdenticon, identicon, vizhash o ninguno). |

### Editor y Visualización

| Característica | Descripción |
|---------|-------------|
| 📝 **Renderizado Markdown** | Escribe pastes en Markdown con una pestaña de vista previa en vivo. El HTML renderizado se sanitiza mediante DOMPurify para prevenir XSS. |
| 🎨 **Resaltado de Sintaxis** | Resaltado de sintaxis automático para más de 100 lenguajes de programación mediante Google Prettify. Elige entre 5 temas: Default, Desert, Doxy, Sons of Obsidian, Sunburst. |
| 📋 **Copiar con un Clic** | Copia el contenido del paste o el enlace para compartir con un solo clic. Una confirmación visual indica que la acción de copia se realizó. |
| ↹ **Soporte de Tabulación** | La tecla Tab inserta un carácter de tabulación en el editor (en lugar de mover el foco). Se activa/desactiva con `Ctrl+M` o `Esc`. |

### Compartir y Distribución

| Característica | Descripción |
|---------|-------------|
| 📱 **Generación de Código QR** | Genera un código QR para cualquier URL de paste. Escanea con un teléfono para abrir el paste instantáneamente. |
| ✉️ **Compartir por Email** | Comparte enlaces de paste por email con un clic. Opcionalmente convierte las marcas de tiempo a UTC para privacidad de zona horaria. |
| 🔗 **Integración con Acortadores de URL** | Integración incorporada con [YOURLS](https://yourls.org/) y [Shlink](https://shlink.io/) para crear enlaces más cortos para compartir. |
| 🗑️ **Enlaces de Eliminación** | Cada paste obtiene un enlace de eliminación único. Compártelo por separado para permitir que personas específicas eliminen el paste. |

### Interfaz de Usuario

| Característica | Descripción |
|---------|-------------|
| 🌙 **Modo Oscuro** | Alternador de tema oscuro/claro integrado con transiciones CSS suaves. Sigue la preferencia del sistema por defecto. Diseño de morfismo de vidrio inspirado en Apple. |
| 🌍 **54 Idiomas** | Internacionalización completa con traducciones a 54 idiomas. Soporta idiomas RTL (árabe, hebreo, farsi, kurdo). Detección automática del idioma del navegador. |
| 🎭 **Sistema de Plantillas** | Alterna entre Bootstrap 5 (moderno, predeterminado) y Bootstrap 3 (clásico). Se puede configurar por instancia o seleccionable por el usuario. |
| 📱 **Diseño Responsive** | Diseño completamente responsive que funciona en escritorio, tablet y móvil. Controles táctiles amigables. |

### Infraestructura

| Característica | Descripción |
|---------|-------------|
| 💾 **6 Backends de Almacenamiento** | Sistema de archivos (predeterminado), MySQL, PostgreSQL, SQLite, Google Cloud Storage, AWS S3 / Ceph. Cambia de backend con un solo cambio de configuración. |
| 🚦 **Limitación de Tasa** | Limitación configurable de peticiones por IP con exenciones de subred. Previene el abuso sin bloquear a usuarios legítimos. |
| 🔒 **SRI (Integridad de Subrecursos)** | Todos los archivos JavaScript incluyen hashes de integridad SHA-512. El navegador verifica la integridad del archivo antes de ejecutarlo, previniendo la manipulación. |
| 📦 **CSP (Política de Seguridad de Contenido)** | Cabeceras CSP estrictas bloquean XSS, clickjacking y carga de recursos no autorizados. Configurable para despliegues personalizados. |
| 🌐 **Soporte PWA** | El manifiesto de aplicación web permite "Añadir a pantalla de inicio" en dispositivos móviles. Funciona como una aplicación independiente. |

---

## 🏗️ Arquitectura

### Cómo Funciona el Cifrado

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

### Resumen del Flujo de Datos

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

### Arquitectura MVC

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

> **Punto clave:** La clave de cifrado se coloca en el fragmento de la URL (`#`), que **nunca se envía al servidor** según la especificación HTTP ([RFC 3986 §3.5](https://tools.ietf.org/html/rfc3986#section-3.5)). El servidor es físicamente incapaz de acceder a la clave de descifrado.

---

## 🛠️ Stack Tecnológico

### Backend

| Tecnología | Versión | Propósito |
|-----------|---------|---------|
| **PHP** | 7.4+ / 8.0+ | Lógica del lado del servidor, arquitectura MVC, autoloading PSR-4 |
| **Composer** | Última | Gestión de dependencias PHP y autoloading |
| **jdenticon** | 2.0.0 | Generación de identicons para avatares de comentarios anónimos |
| **ip-lib** | 1.22.0 | Análisis de direcciones IP/subredes para limitación de tasa y control de acceso |
| **identicon** | 2.0.0 | Estilo alternativo de identicon (similar a GitHub) |
| **polyfill-php80** | 1.33.0 | Compatibilidad con PHP 8.0 para entornos PHP 7.4 |

### Frontend

| Tecnología | Versión | Propósito |
|-----------|---------|---------|
| **jQuery** | 3.7.1 | Manipulación del DOM, AJAX, manejo de eventos |
| **Bootstrap** | 5.3.8 | Grid responsive, componentes, modo oscuro (variables CSS) |
| **Bootstrap Icons** | SVG sprite | Más de 50 iconos usados en toda la interfaz |
| **Showdown** | 2.1.0 | Renderizado de Markdown a HTML con soporte GFM |
| **DOMPurify** | 3.3.2 | Sanitización de HTML para prevenir XSS en Markdown renderizado |
| **Google Prettify** | Última | Resaltado de sintaxis para más de 100 lenguajes |
| **kjua** | 0.10.0 | Generación de códigos QR (basado en canvas, sin dependencia del servidor) |
| **zlib** | 1.3.1 | Compresión/descompresión vía WebAssembly (más rápido que JS) |
| **base-x** | 5.0.1 | Codificación Base58 para claves de cifrado seguras en URL |

### Cifrado

| Componente | Detalle |
|-----------|--------|
| **Algoritmo** | AES-256-GCM (modo Galois/Counter) — cifrado autenticado con datos asociados (AEAD) |
| **API** | [WebCrypto API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Crypto_API) — nativa del navegador, acelerada por hardware |
| **Tamaño de Clave** | 256 bits (generada aleatoriamente por paste) |
| **IV** | Vector de inicialización aleatorio de 96 bits |
| **Etiqueta de Autenticación** | Etiqueta de autenticación de 128 bits (garantiza la integridad de los datos) |
| **Implementación** | 100% en el lado del cliente — el servidor nunca ve texto plano ni claves |

### Pruebas

| Tecnología | Propósito |
|-----------|---------|
| **PHPUnit** | 9.x — Pruebas unitarias PHP (29 archivos de prueba) |
| **Mocha** | Pruebas unitarias JS (15 módulos de prueba) |
| **jsdom** | Simulación del DOM del navegador para pruebas JS |
| **NYC** | Informes de cobertura de código |

---

## ⚙️ Configuración

Toda la configuración se realiza mediante `cfg/conf.php`. Comienza desde el archivo de ejemplo:

```sh
cp cfg/conf.sample.php cfg/conf.php
```

### Referencia Completa de Configuración

#### `[main]` — Configuración General

| Opción | Tipo | Predeterminado | Descripción |
|--------|------|---------|-------------|
| `name` | string | `"zbin"` | Nombre mostrado en la interfaz y pestaña del navegador |
| `basepath` | string | auto-detectado | URL completa con barra final (necesaria para imágenes OpenGraph) |
| `discussion` | bool | `true` | Habilitar comentarios con hilos en los pastes |
| `opendiscussion` | bool | `false` | Pre-seleccionar la casilla de discusión |
| `password` | bool | `true` | Habilitar la opción de protección con contraseña |
| `fileupload` | bool | `false` | Habilitar la carga de archivos adjuntos |
| `burnafterreadingselected` | bool | `false` | Pre-seleccionar destruir después de leer |
| `defaultformatter` | string | `"plaintext"` | Formato predeterminado: `plaintext`, `syntaxhighlighting`, `markdown` |
| `syntaxhighlightingtheme` | string | ninguno | Tema: `desert`, `doxy`, `sons-of-obsidian`, `sunburst` |
| `sizelimit` | int | `10000000` | Tamaño máximo del paste en bytes (10 MB) |
| `template` | string | `"bootstrap5"` | Plantilla de interfaz: `bootstrap5` o `bootstrap` |
| `templateselection` | bool | `false` | Mostrar selector de plantilla en la interfaz |
| `languageselection` | bool | `false` | Mostrar selector de idioma desplegable |
| `languagedefault` | string | auto-detectado | Código de idioma predeterminado (ej., `"en"`, `"es"`) |
| `urlshortener` | string | ninguno | Endpoint de API del acortador de URL |
| `qrcode` | bool | `true` | Habilitar botón para compartir código QR |
| `email` | bool | `true` | Habilitar botón para compartir por email |
| `icon` | string | `"jdenticon"` | Estilo de avatar en comentarios: `none`, `identicon`, `jdenticon`, `vizhash` |
| `compression` | string | `"zlib"` | Compresión: `"zlib"` o `"none"` |
| `httpwarning` | bool | `true` | Mostrar advertencia cuando no se usa HTTPS |

#### `[expire]` — Configuración de Expiración

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

#### `[traffic]` — Limitación de Tasa

```ini
[traffic]
limit = 10                  ; Seconds between requests per IP (0 = disabled)
; exempted = "10.0.0/8"     ; Exempt subnets (comma-separated)
; creators = "10.0.0/8"     ; Restrict paste creation to these IPs
; header = "X_FORWARDED_FOR" ; IP header when behind a reverse proxy
```

#### `[purge]` — Limpieza Automática

```ini
[purge]
limit = 300                 ; Seconds between purge runs (0 = every request)
batchsize = 10              ; Max expired pastes to delete per run (0 = disabled)
```

#### `[formatter_options]` — Etiquetas de Formato

```ini
[formatter_options]
plaintext = "Plain Text"
syntaxhighlighting = "Source Code"
markdown = "Markdown"
```

---

## 🔒 Seguridad

### Arquitectura de Conocimiento Cero

zbin implementa un diseño de **conocimiento cero** donde el servidor es matemáticamente incapaz de acceder al contenido de los pastes:

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

### Capas de Seguridad

| Capa | Mecanismo | Propósito |
|-------|-----------|---------|
| **Cifrado** | AES-256-GCM | Confidencialidad + integridad de los datos del paste |
| **Aislamiento de clave** | Fragmento de URL (`#`) | El servidor nunca recibe la clave de descifrado |
| **Contraseña** | Segundo factor opcional | Protección adicional incluso si la URL se filtra |
| **SRI** | Hashes SHA-512 | Asegura que los archivos JS no han sido manipulados |
| **CSP** | Política de Seguridad de Contenido | Bloquea XSS, clickjacking y recursos no autorizados |
| **Sanitización** | DOMPurify | Elimina HTML malicioso de la salida Markdown |
| **Limitación de tasa** | Limitación por IP | Previene fuerza bruta y abuso |
| **Detección de HTTPS** | Banner de advertencia | Alerta a los usuarios sobre conexiones inseguras |
| **Detección de bots** | Filtrado de user-agent | Previene que los bots activen destruir-después-de-leer |

### ¿Por qué AES-256-GCM?

| Propiedad | Beneficio |
|----------|---------|
| **Clave de 256 bits** | Computacionalmente inviable de descifrar por fuerza bruta (2²⁵⁶ combinaciones) |
| **Modo GCM** | Cifrado autenticado — detecta si el texto cifrado fue modificado |
| **Aceleración por hardware** | WebCrypto usa instrucciones CPU AES-NI cuando están disponibles |
| **Aprobado por NIST** | Utilizado por gobiernos y fuerzas militares en todo el mundo |

### Política de Seguridad de Contenido

CSP predeterminada (la más restrictiva):

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

### Modelo de Amenazas

| Amenaza | Mitigación |
|--------|-----------|
| Compromiso del servidor | Los datos están cifrados; el atacante solo obtiene texto cifrado |
| Hombre en el medio | HTTPS + SRI + CSP previenen la inyección |
| Ataque XSS | Sanitización con DOMPurify + CSP estricta |
| Fuerza bruta | Limitación de tasa + espacio de clave de 256 bits |
| Filtración de URL (enlace compartido) | La contraseña opcional añade un segundo factor |
| Espionaje del administrador | Conocimiento cero — el administrador no puede descifrar |

---

## 💾 Backends de Almacenamiento

### Sistema de Archivos (Predeterminado)

La opción más simple — almacena cada paste como un archivo en el directorio `data/`.

```ini
[model]
class = Filesystem
[model_options]
dir = PATH "data"
```

**Ventajas:** Sin dependencias, respaldo fácil (solo copia el directorio)
**Desventajas:** Más lento con millones de pastes, sin replicación incorporada

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

Perfecto para despliegues pequeños — no se necesita base de datos externa.

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

Para Ceph/MinIO (compatible con S3):

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

### Esquema de Base de Datos

Al usar el backend de base de datos, las tablas se crean automáticamente. El esquema:

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

## 🚢 Despliegue

### Despliegue con Docker

El proyecto incluye un `Dockerfile` listo para producción:

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

### Configuración de Nginx

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

### Configuración de Apache

El archivo `.htaccess` incluido maneja la reescritura de URL automáticamente. Asegúrate de que `mod_rewrite` esté habilitado:

```sh
a2enmod rewrite
systemctl restart apache2
```

### Railway / Render / Fly.io

Dado que el proyecto incluye un `Dockerfile`, se despliega directamente en plataformas de contenedores:

1. Conecta tu repositorio de GitHub
2. Selecciona la rama `main`
3. La plataforma auto-detecta el Dockerfile
4. Establece el puerto en `80`
5. Despliega

---

## 📡 API

zbin expone una API JSON simple para la gestión programática de pastes.

### Crear un Paste

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

**Respuesta (éxito):**

```json
{
  "status": 0,
  "id": "abc123",
  "url": "/?abc123",
  "deletetoken": "def456..."
}
```

### Leer un Paste

```http
GET /?abc123 HTTP/1.1
Accept: application/json
X-Requested-With: JSONHttpRequest
```

**Respuesta:**

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

### Eliminar un Paste

```http
DELETE /?abc123 HTTP/1.1
Content-Type: application/json
X-Requested-With: JSONHttpRequest

{
  "pasteid": "abc123",
  "deletetoken": "def456..."
}
```

**Respuesta:**

```json
{
  "status": 0,
  "id": "abc123"
}
```

### Respuestas de Error

```json
{
  "status": 1,
  "message": "Error description here"
}
```

| Estado | Significado |
|--------|---------|
| `0` | Éxito |
| `1` | Error (ver campo `message`) |

---

## 🌍 Internacionalización

zbin soporta **54 idiomas** con detección automática del navegador:

| Idioma | Código | | Idioma | Código | | Idioma | Código |
|----------|------|-|----------|------|-|----------|------|
| Árabe 🇸🇦 | `ar` | | Alemán 🇩🇪 | `de` | | Polaco 🇵🇱 | `pl` |
| Búlgaro 🇧🇬 | `bg` | | Griego 🇬🇷 | `el` | | Portugués 🇵🇹 | `pt` |
| Catalán | `ca` | | Hebreo 🇮🇱 | `he` | | Rumano 🇷🇴 | `ro` |
| Chino 🇨🇳 | `zh` | | Hindi 🇮🇳 | `hi` | | Ruso 🇷🇺 | `ru` |
| Corso | `co` | | Húngaro 🇭🇺 | `hu` | | Eslovaco 🇸🇰 | `sk` |
| Checo 🇨🇿 | `cs` | | Indonesio 🇮🇩 | `id` | | Esloveno 🇸🇮 | `sl` |
| Inglés 🇺🇸 | `en` | | Italiano 🇮🇹 | `it` | | Español 🇪🇸 | `es` |
| Estonio 🇪🇪 | `et` | | Japonés 🇯🇵 | `ja` | | Sueco 🇸🇪 | `sv` |
| Farsi 🇮🇷 | `fa` | | Coreano 🇰🇷 | `ko` | | Tailandés 🇹🇭 | `th` |
| Finés 🇫🇮 | `fi` | | Kurdo | `ku` | | Turco 🇹🇷 | `tr` |
| Francés 🇫🇷 | `fr` | | Noruego 🇳🇴 | `no` | | Ucraniano 🇺🇦 | `uk` |

**Soporte RTL:** Árabe (`ar`), Hebreo (`he`), Farsi (`fa`), Kurdo (`ku`)

### Configuración

```ini
; Set a fixed language (disables auto-detection)
languagedefault = "es"

; Or enable the language selector dropdown
languageselection = true
```

### Añadir un Nuevo Idioma

1. Copia `i18n/en.json` a `i18n/xx.json` (tu código de idioma)
2. Traduce todos los valores de las cadenas de texto
3. Añade la entrada a `i18n/languages.json`

---

## 📁 Estructura del Proyecto

```
zbin/
├── cfg/                          # Configuración
│   ├── .htaccess                 # Bloquear acceso directo a la configuración
│   └── conf.sample.php           # Configuración de ejemplo (copiar a conf.php)
│
├── css/                          # Hojas de estilo
│   ├── bootstrap5/               # Tema Bootstrap 5
│   │   ├── bootstrap-5.3.8.css   # Framework Bootstrap
│   │   ├── bootstrap.rtl-5.3.8.css  # Variante RTL
│   │   └── zbin.css              # Estilos personalizados inspirados en Apple
│   ├── bootstrap/                # Tema legado Bootstrap 3
│   │   ├── bootstrap-3.4.1.css
│   │   ├── darkstrap-0.9.3.css   # Tema oscuro
│   │   ├── zbin.css
│   │   └── fonts/                # Fuentes Glyphicon
│   ├── prettify/                 # Temas de resaltado de sintaxis (5)
│   ├── common.css                # Estilos compartidos (todas las plantillas)
│   └── noscript.css              # Alternativa para navegadores sin JavaScript
│
├── i18n/                         # 54 archivos de traducción de idiomas (JSON)
│
├── img/                          # Iconos e imágenes
│   ├── icon.svg                  # Icono de la app (hexágono con cerradura)
│   ├── logo.svg                  # Logo completo (icono + texto)
│   ├── bootstrap-icons.svg       # Sprite de iconos (más de 50 iconos)
│   ├── favicon.ico               # Favicon del navegador
│   ├── apple-touch-icon.png      # Icono de pantalla de inicio iOS
│   ├── android-chrome-*.png      # Iconos PWA Android
│   └── mstile-*.png              # Iconos de mosaico Windows
│
├── js/                           # JavaScript del frontend
│   ├── zbin.js                   # Aplicación principal (más de 6000 líneas, 20 módulos)
│   ├── common.js                 # Utilidades compartidas de prueba
│   ├── legacy.js                 # Verificaciones de compatibilidad del navegador
│   ├── jquery-3.7.1.js           # Biblioteca jQuery
│   ├── bootstrap-5.3.8.js        # Componentes JS de Bootstrap
│   ├── showdown-2.1.0.js         # Renderizador Markdown
│   ├── purify-3.3.2.js           # Sanitizador HTML (protección XSS)
│   ├── prettify.js               # Resaltador de sintaxis
│   ├── kjua-0.10.0.js            # Generador de códigos QR
│   ├── zlib-1.3.1-2.js           # Compresión (cargador JS)
│   ├── zlib-1.3.1.wasm           # Compresión (binario WebAssembly)
│   ├── base-x-5.0.1.js           # Codificación base para claves
│   ├── dark-mode-switch.js       # Lógica de alternancia de tema
│   └── test/                     # 15 módulos de prueba Mocha
│
├── lib/                          # Backend PHP (espacio de nombres Zbin, PSR-4)
│   ├── Controller.php            # Controlador MVC principal (enrutamiento de peticiones)
│   ├── Configuration.php         # Análisis y validación de archivo de configuración
│   ├── Model.php                 # Fábrica del modelo de datos (abstracción de almacenamiento)
│   ├── View.php                  # Motor de renderizado de plantillas
│   ├── Request.php               # Análisis de peticiones HTTP
│   ├── Filter.php                # Formateo de entrada/salida
│   ├── I18n.php                  # Internacionalización (54 idiomas)
│   ├── Json.php                  # Utilidades JSON
│   ├── FormatV2.php              # Conversión de formato de datos
│   ├── TemplateSwitcher.php      # Lógica de selección de plantilla
│   ├── Vizhash16x16.php          # Hash visual (avatares basados en IP)
│   ├── Data/                     # Backends de almacenamiento
│   │   ├── AbstractData.php      # Interfaz de almacenamiento
│   │   ├── Filesystem.php        # Almacenamiento basado en archivos
│   │   ├── Database.php          # Almacenamiento en base de datos PDO
│   │   ├── GoogleCloudStorage.php
│   │   └── S3Storage.php         # AWS S3 / Ceph
│   ├── Model/                    # Modelos de datos
│   │   ├── AbstractModel.php     # Modelo base
│   │   ├── Paste.php             # CRUD + validación de pastes
│   │   └── Comment.php           # CRUD + validación de comentarios
│   ├── Persistence/              # Estado del lado del servidor
│   │   ├── AbstractPersistence.php
│   │   ├── ServerSalt.php        # Salt HMAC para tokens de eliminación
│   │   ├── TrafficLimiter.php    # Limitación de tasa por IP
│   │   └── PurgeLimiter.php      # Limitación de limpieza de pastes expirados
│   ├── Proxy/                    # Proxies de acortadores de URL
│   │   ├── AbstractProxy.php
│   │   ├── YourlsProxy.php       # Integración con YOURLS
│   │   └── ShlinkProxy.php       # Integración con Shlink
│   └── Exception/                # Excepciones personalizadas
│       ├── JsonException.php
│       └── TranslatedException.php
│
├── tpl/                          # Plantillas HTML (PHP)
│   ├── bootstrap5.php            # Plantilla moderna (predeterminada)
│   ├── bootstrap.php             # Plantilla clásica Bootstrap 3
│   └── shortenerproxy.php        # Página proxy del acortador de URL
│
├── tst/                          # Pruebas unitarias PHP (29 archivos)
│   ├── phpunit.xml               # Configuración de PHPUnit
│   └── *.php                     # Clases de prueba
│
├── index.php                     # Punto de entrada de la aplicación
├── Dockerfile                    # Definición del contenedor Docker
├── .htaccess                     # Reescritura de URL de Apache
├── composer.json                 # Dependencias PHP
├── manifest.json                 # Manifiesto PWA
├── browserconfig.xml             # Configuración de mosaicos Windows
├── robots.txt                    # Directivas para motores de búsqueda
├── Makefile                      # Automatización de construcción
├── CHANGELOG.md                  # Historial de versiones
└── LICENSE.md                    # Licencia MIT
```

---

## ❓ FAQ

### "Cargando..." nunca desaparece

Esto generalmente significa que JavaScript no se pudo cargar. Causas comunes:

1. **Discrepancia de hash SRI** — Si modificaste algún archivo `.js`, debes regenerar su hash SHA-512 en `lib/Configuration.php`. Usa:
   ```sh
   openssl dgst -sha512 -binary js/zbin.js | openssl base64 -A
   ```
2. **Contenido mixto** — Servir el sitio por HTTP pero los recursos referencian HTTPS (o viceversa)
3. **CSP bloqueando** — Las cabeceras CSP personalizadas pueden bloquear la ejecución de scripts. Revisa la consola del navegador.
4. **Proxy inverso** — Asegúrate de que tu proxy reenvíe todas las cabeceras de petición correctamente

### ¿Puede el administrador del servidor leer mis pastes?

**No.** La clave de cifrado existe solo en el fragmento de la URL (`#`), que el navegador nunca envía al servidor. El administrador puede ver blobs cifrados pero no puede descifrarlos sin la clave.

### ¿Qué pasa si pierdo la URL?

El paste es **irrecuperable**. No hay "olvidé mi contraseña" ni recuperación de clave. Esto es por diseño — si existiera un mecanismo de recuperación, significaría que el servidor tiene acceso a las claves, rompiendo el conocimiento cero.

### ¿Cómo funciona destruir-después-de-leer?

1. El paste se marca como "destruir después de leer" en el servidor
2. Cuando alguien abre la URL, el navegador primero muestra un diálogo de confirmación
3. Solo después de hacer clic en "Sí, verlo" el servidor elimina el paste y devuelve los datos
4. El paste desaparece permanentemente — incluso la URL original devolverá un 404

### ¿Es zbin adecuado para datos sensibles?

zbin proporciona cifrado fuerte (AES-256-GCM) y una arquitectura de conocimiento cero. Sin embargo:
- La URL contiene la clave de descifrado — cualquier persona con la URL puede leer el paste
- Para máxima seguridad, también establece una contraseña fuerte
- Usa HTTPS para prevenir la interceptación de la URL
- Considera "destruir después de leer" para secretos de un solo uso

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas! Aquí te explicamos cómo empezar:

### Configuración del Entorno de Desarrollo

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

### Estilo de Código

- **PHP:** Estándar de codificación PSR-12, autoloading PSR-4 bajo el espacio de nombres `Zbin\`
- **JavaScript:** ESLint configurado, patrón de módulo jQuery
- **CSS:** Nomenclatura inspirada en BEM con prefijo `--zb-` para propiedades personalizadas

### Guía para Pull Requests

1. Haz un fork del repositorio
2. Crea una rama de funcionalidad (`git checkout -b feature/my-feature`)
3. Realiza tus cambios
4. Ejecuta las pruebas (`make test`)
5. Si modificaste algún archivo JS, regenera los hashes SRI
6. Haz commit con un mensaje claro
7. Haz push y abre un Pull Request

---

## 📄 Licencia

Licenciado bajo la **[Licencia MIT](LICENSE.md)**.

Copyright (c) 2026 [eyvergomz](https://github.com/eyvergomz)

Consulta [LICENSE.md](LICENSE.md) para el texto completo de la licencia y las atribuciones de bibliotecas de terceros.

<div align="center">
<br/>

---

<sub>Construido con 🔒 por <a href="https://github.com/eyvergomz">eyvergomz</a></sub>

</div>
