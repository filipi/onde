[![PHP](https://img.shields.io/badge/PHP-^7.2--^8.2-blue?style=for-the-badge&logo=php&logoColor=white)]
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-%E2%89%A56.0-blue?style=for-the-badge&logo=postgresql&logoColor=white)]
[![License MIT](https://img.shields.io/badge/License-MIT-green?style=flat-square)]

# ONDE – PHP Database‑Driven Form & CRUD Micro‑Framework 🚀

ONDE (short for *“Onde was Not Developed for aEsthetics”*) is a minimalist PHP “flame work” that dynamically generates forms and CRUD interfaces from **PostgreSQL configuration**, with no need to write controllers, models, or view templates.

Inspired by the [exflickr/flamework](https://github.com/exflickr/flamework), 
Onde is a quick and dirty PHP application prototyping fLame Work.

## Automatic form CRUD (forms.php)

In its current version, it serves as the basis for a simple PHP application,
providing functions to generate HTML tables from PostgreSQL queries and also a
module to automatically generate CRUD forms for PostgreSQL tables.

The CRUD form generation tool scans the database data dictionary to
identify relations making selection boxes (or radio lists, depending on the
size of the list) for the 1:N relations and check boxes lists for the
N:N relations.

The tool handles the submitted information through an "insert" or a "save" button,
thus automatically generating an insert or an update query statement.
There is no object relational abstraction, the queries are generated directly
in the PHP functions. 

## Automatic Menus from table menu (databasemenu.php)

The framework provides a side menu (usually on the left side of the window),
which is generated from table menu's data. The menu items could link
to a php script, or a forms.php's form, or a calendar.

## User access control

The user's access control is made through limiting the access of
user's groups. Each user is assigned to a group (or groups) and the
forms and menus are cleared for the groups.

It is possible to clear some forms to be accessible without authentication.

---

## 🔍 Key Features

- **Declarative PostgreSQL configuration**  
  Use tables like `forms`, `campos`, `eventosdeemail`, and `emailtemplates` to define your UI; ONDE auto-generates everything — including file uploads, CSV exports, duplication, and email notification actions.

- **Schema‑aware UI generation**  
  Introspects `information_schema` to derive relationships (1:N, N:N), SQL-backed selects, filters, layouts, and menu items automatically.

- **Native file upload/download**  
  Handles file-type fields (`arquivo`) using `bytea`, preserving filename, MIME type, and size metadata.

- **Email triggers via PHPMailer**  
  Send formatted emails (HTML + text) tied to form events automatically, configured entirely within PostgreSQL.

- **Form builder UI with rich editing**
  - Define and configure forms through dedicated CRUD forms for `forms` and `menus` tables.
  - Inject custom JavaScript/CSS per form.
  - Edit SQL-backed field definitions with **CodeMirror syntax highlighting**.
  - Drag-and-drop ordering of fields for users in the `developers` group.

- **Secure authentication & session management**
  - Login via `frm_login.php` → `auth.php`, session scoped as `onde`.
  - Logout via `logout.php` clears session and session files.

- **Secure password reset**
  - User requests reset via `resetSenha.php`.
  - Time-limited SHA‑256 token written to `reseta_senha`.
  - Reset link sent by email; token valid for 10 minutes only.
  - `doResetPass.php` validates token, enforces password rules, updates hashed password, and auto‑logs in the user.

- **Modern password hashing**
  - Legacy support uses `crypt()`.
  - On PHP 8+, ONDE switches to `password_hash()` with **Argon2**, aligning with OWASP standards.

---

## 🚧 Limitations & Security Considerations

- **PostgreSQL-only**, no support for other databases.
- **Session basics only**: logout UI included; consider adding session expiration, regeneration, and Secure/HttpOnly cookie flags.
- **Validation enhancements needed**: implement CSRF tokens, XSS/SQL injection guards, and stronger password policies/advice.
- **No visual form designer**: form definitions require SQL updates; drag/drop and CodeMirror help, but no GUI builder.

---

## 📦 Quickstart Example

```sql
-- 1. Create your main table
CREATE TABLE tasks (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  assignee TEXT,
  due_date DATE,
  attachment BYTEA,
  filename TEXT,
  mimetype TEXT,
  filesize INTEGER
);

-- 2. Declare the form (referenced by numeric ID)
INSERT INTO forms (
  nome, sql, texto, inserir, editar, deletar, duplicar,
  ordem, login, emailtemplate
) VALUES (
  'tasks',
  'SELECT * FROM tasks ORDER BY id DESC',
  'Task Manager',
  true, true, true, true,
  'id DESC',
  true,
  'email_new_task'
);

-- 3. Define form fields
INSERT INTO campos (
  formulario, nome, tipo, texto, obrigatorio
) VALUES
('tasks','title','texto','Title',true),
('tasks','description','textarea','Description',false),
('tasks','assignee','texto','Assignee',false),
('tasks','due_date','data','Due Date',false),
('tasks','attachment','arquivo','Attachment',false);

-- 4. Set up email notification
INSERT INTO emailtemplates (
  nome, assunto, corpo_html, corpo_texto
) VALUES ( ... );
INSERT INTO eventosdeemail (
  formulario, evento, template, para
) VALUES (
  (SELECT id FROM forms WHERE nome='tasks'),
  'insercao',
  'email_new_task',
  'manager@example.com'
);

-- 5. Access the task form via numeric ID
http://<your-site>/forms.php?form=<<TASK_FORM_ID>>

✔ ONDE will instantly render a complete CRUD interface — list, create, edit, delete, duplicate, file upload, email notifications — all without writing a single line of PHP.


### 🔐 Password & Authentication Flow

ONDE delivers a modern, secure user authentication flow:

- **Login/logout UI** with session handling.
- **Secure reset flow** using time‑restricted SHA‑256 tokens (10 min expiry), with reset UI and feedback.
- **Hybrid hashing**: retains legacy support with `crypt()` but defaults to Argon2 via `password_hash()` in PHP 8+.

---

### 🛠️ Extendability & Enhancements

Consider adding:

| Enhancement                         | Benefit                                               |
|-------------------------------------|--------------------------------------------------------|
| `session_regenerate_id()`          | Mitigates session fixation                            |
| Secure/HttpOnly/SameSite flags     | Protects session cookies                              |
| CSRF tokens                        | Prevents cross-site request forgery                  |
| XSS/SQL sanitizers                 | Guards against injection vulnerabilities             |
| Better password policy             | Enforce ≥8 chars, uppercase, digits, etc.            |
| Multitenancy support               | Filter by `tenant_id` or distinct DB schemas         |
| Theming/UI templates               | Custom CSS/JS per form already supported             |

---

### 🧠 Philosophy

ONDE embodies true zero‑code CRUD: you declare your interface in PostgreSQL and it just works. Best suited for internal tools and prototypes. For enterprise-grade or public-facing systems, consider maturity platforms like Symfony, Laravel, or Oracle APEX.

---

### 📂 Project Structure

web/
├─ forms.php
├─ frm_login.php
├─ auth.php
├─ logout.php
├─ resetSenha.php
├─ doResetPass.php
include/
├─ startup.inc
├─ start_sessao.inc
├─ lib.inc

---

### 🤝 Contribute

Authored by *filipi*. Issues and PRs for feature improvements (session security, Argon2 tuning, UI enhancements, multitenancy) are welcome.

Enjoy fast, secure, database-driven admin development with ONDE! 😊

---

**Let me know if you'd like to:**  
- Add a flow diagram,  
- Include screenshots,  
- Showcase drag-and-drop field ordering or CodeMirror in the form builder,  
- Or compare ONDE to specific frameworks within the README.  
