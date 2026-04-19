# TUNAI LMS — Application Architecture Blueprint
> **Purpose:** This document provides a high-level footprint of the TUNAI LMS application architecture. Use this as a reference guide when adding new modules, integrating features, or modifying core logic to ensure nothing breaks during development.

---

## 1. Directory Architecture: The "Decoupled" Approach
The application intentionally separates the Laravel codebase from the course content:

```text
S:\tunai\
  ├── webapp/                 ← The main Laravel 12 application
  │   ├── app/Models/         ← Database models (User, Diagram, GameSession, etc.)
  │   ├── app/Services/       ← Core logic (CourseService, MarkdownService, GameService)
  │   └── config/course.php   ← Config that points to the 'corse' directory
  │
  └── corse/                  ← External, version-controlled course content
      ├── Course_Name/
      │   ├── course.md                    ← Course metadata (title, order)
      │   └── Module_01_Name/              ← Module directories
      │       ├── theoretical/01_Lesson.md ← Actual lesson content (Markdown)
      │       ├── practical/
      │       └── examples/
      └── RGSOC_Academy/
```
**Why do this?**
Placing the `corse` folder outside the web root (`webapp/public`) provides security. Users cannot guess URLs to download raw markdown. The app reads and parses the content, ensuring proper access control checks before serving it.

---

## 2. The Content Engine (Flat-File CMS)
The heart of the learning system is built around raw Markdown files.

### Key Services
- **`CourseService.php`:** The filesystem scanner. It enumerates the `corse/` directory, extracts courses, modules, and lessons, and builds the hierarchical structure.
- **`MarkdownService.php`:** Parses YAML front-matter (metadata) from `.md` files and converts the remaining text into HTML.
- **`QuizService.php`:** Identifies quiz blocks within lessons, manages state, and grades quiz attempts based on the parsed markdown data.

### How to Add Content
1. **Manual / Git-based:** You can manually create folders and `.md` files inside the `corse/` directory. Once committed and pulled to the server, they are instantly live.
2. **Admin UI:** Log into the app as an `admin`. The `Admin\ContentController.php` provides a full web interface to create, edit, save, and publish `.md` files directly.

---

## 3. Role-Based Access Control (RBAC)
Access constraints are managed directly in `app/Models/User.php`. Capabilities are strictly enforced based on tiers:

| Role | Max Courses | Workshops | Permissions |
|---|---|---|---|
| **`admin`** | Unlimited | Yes | Full access to everything, admin dashboard, bypasses constraints. |
| **`cstudent`**| Unlimited | Yes | "Certified Student" - access to all courses and workshops. |
| **`student`** | 3 | Yes | Standard tier. Capped at 3 courses. |
| **`guest`** | 1 | No | Extremely limited. Good for lead generation/trials. |
| **`preenrol`**| 0 | No | Holding tier. No access to content. |

*Note: Access to specific modules and content is checked on every request inside the `CourseController` using capabilities like `$user->canAccessModule()`.*

---

## 4. Main App Modules
If you are developing new features, here are the main existing sub-systems to be aware of:

1.  **Markdown Course Engine:** Handles parsing and routing of lessons. Automatically marks lessons as complete (`modules_viewed` tracking in the `User` model).
2.  **Virtual Diagrams:** While text lessons are `.md` files, the `diagrams` section is "virtual". The `Diagram` model pulls diagrams from the MySQL database and injects them seamlessly into the course lesson flow.
3.  **CyberBreach Game (`GameService`):** A gamified training module featuring logic for `game_sessions`, `game_teams`, `game_rounds`, and `game_cards`.
4.  **Admin Panel:** Full CRUD capability for content, users, classes, and invitations.
5.  **Classes/Cohorts:** Users can be grouped into classes (`class_user` pivot table), and courses can be assigned directly to whole classes (`class_course_enrollments`).

---

## 5. Development Impact
When adding new modules:
- Do not store heavy media/videos in the Git repo. Rely on the `media_library` or external hosting like S3/DigitalOcean Spaces to prevent repo bloat.
- When creating new lesson types, update the `$elementCategories` in `CourseService.php` and `config/course.php`.
- Always respect the established `User::getCapability()` methods if you create new gating mechanisms.
