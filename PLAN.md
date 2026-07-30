You are a Principal Software Architect with 20+ years of experience building enterprise software.

Your background includes:

- WordPress Core
- WooCommerce
- Shopify
- Laravel
- Symfony
- SOLID Architecture
- PSR Standards
- Object-Oriented PHP
- Clean Architecture
- Domain Driven Design
- Performance Engineering

Your task is NOT to build a WordPress theme.

Your task is to design a complete software architecture for a commercial WooCommerce platform that will be sold worldwide.

====================================================

PROJECT VISION

We are building a Commerce Platform for WordPress.

The platform consists of:

1. Traditional WordPress Theme
2. Core Plugin
3. Industry Starter Kits
4. Premium Modules
5. Cloud Integration (future)

The goal is to launch stores quickly using reusable components.

Every client should start from the same platform.

No duplicated development.

====================================================

IMPORTANT PHILOSOPHY

We are NOT building another WordPress theme.

We are building a software platform.

The theme is only the presentation layer.

All business logic belongs inside the Core Plugin.

====================================================

ARCHITECTURE REQUIREMENTS

The solution must follow:

SOLID

DRY

KISS

YAGNI

PSR-4

WordPress Coding Standards

WooCommerce Best Practices

OOP

Namespaced Code

Dependency Injection where appropriate

Autoloading

Service Providers

Hooks & Filters

High Performance

High Scalability

Developer Friendly

====================================================

THEME REQUIREMENTS

Traditional WordPress Theme.

NOT Full Site Editing.

NOT Gutenberg Theme.

Compatible with:

WordPress

WooCommerce

Classic Widgets

Customizer (only where appropriate)

The theme must ONLY contain:

Presentation

Templates

Layouts

CSS

JavaScript

Template Parts

Theme Setup

Assets

Minimal Helper Functions

NO business logic.

====================================================

THEME STRUCTURE

Design a complete folder structure.

Example:

theme/

assets/

css/

js/

images/

fonts/

inc/

Core/

Admin/

WooCommerce/

Performance/

Helpers/

Customizer/

Hooks/

Setup/

template-parts/

woocommerce/

page-templates/

languages/

functions.php

style.css

screenshot.png

Include every folder with explanation.

====================================================

CORE PLUGIN

Design the Commerce Core plugin.

Responsibilities:

Dashboard

Store Setup Wizard

Theme Integration

Performance Engine

Header Builder

Footer Builder

Layout Manager

Mega Menu

AJAX Search

Wishlist

Compare

Quick View

Product Badges

Developer API

REST API

Settings

License System

Analytics

Import/Export

Brand Manager

Asset Manager

Update Manager

Module Manager

Everything should be modular.

====================================================

MODULE SYSTEM

Every feature must be installable.

Example

Commerce Core

↓

Modules

Wishlist

Compare

Noorifa

NoorQuiz

Marketing

Analytics

SEO

Performance

Each module must be independent.

====================================================

OOP

Everything inside Core Plugin must be Object Oriented.

Use:

Namespaces

Interfaces

Traits only when necessary

Abstract classes only when beneficial

Constructor Injection

Dependency Injection

Service Container

Service Providers

Singleton only when justified

Repositories where appropriate

Factories where appropriate

Events

Hooks

Avoid static classes unless truly necessary.

====================================================

AUTOLOADING

Use PSR-4.

Composer friendly.

Explain how classes are loaded.

====================================================

BOOTSTRAP

Explain how the entire application boots.

Theme

↓

Core Plugin

↓

Service Container

↓

Service Providers

↓

Modules

↓

WordPress Hooks

↓

Render

====================================================

SERVICE CONTAINER

Design a lightweight dependency injection container.

Explain:

Registration

Resolution

Lifecycle

====================================================

PERFORMANCE

The architecture must prioritize speed.

Explain:

Conditional asset loading

Lazy loading

Code splitting

Caching

Image optimization

Database optimization

Transient usage

Object cache

Script loading

CSS loading

Avoid unnecessary queries

====================================================

DATABASE

Explain:

Custom tables

When to use wp_options

When to use post meta

When to use taxonomy

When to use custom tables

Migration strategy

Versioning

====================================================

WOOCOMMERCE

Explain best practices.

Template overrides

Hooks

Filters

Custom product layouts

Checkout

Cart

Emails

Account

Performance

Compatibility

====================================================

DEVELOPER EXPERIENCE

Other developers should understand the project quickly.

Explain:

Folder conventions

Naming conventions

Coding standards

Documentation

PHPDoc

Architecture diagrams

====================================================

FUTURE CLOUD

The architecture should support future cloud services.

Examples:

License Server

Template Marketplace

AI

Analytics

Remote Updates

Cloud Sync

Without requiring major rewrites.

====================================================

DELIVERABLE

Produce a professional Software Architecture Document.

Include:

1. Executive Summary
2. Vision
3. Architecture Principles
4. Folder Structure
5. Boot Process
6. Theme Architecture
7. Core Plugin Architecture
8. Module System
9. Service Container
10. OOP Design
11. Performance Strategy
12. Database Strategy
13. WooCommerce Integration
14. Security Strategy
15. Coding Standards
16. Deployment Strategy
17. Future Roadmap

Include UML-style ASCII diagrams where helpful.

Explain WHY each architectural decision was made.

Do not write any implementation code.

Think like a Principal Architect designing software that will be maintained for the next 10 years by multiple developers.


====================================================

Ecombon Platform

├── Ecombon Theme
│
├── Ecombon Core
│   ├── Bootstrap
│   ├── Service Container
│   ├── Settings
│   ├── Theme Integration
│   ├── Module Loader
│   └── Developer API
│
├── Ecombon Performance
├── Ecombon Search
├── Ecombon Wishlist
├── Ecombon Compare
├── Ecombon Mega Menu
├── Noorifa
├── NoorQuiz
└── Future Modules


====================================================

Phase 1 — Build the Perfect Static Theme

Forget about settings.

Forget about options.

Forget about Customizer.

Forget about admin pages.

Just build the frontend.

====================================================

Phase 1 — STATUS: COMPLETE

The Ecombon theme is a real, working, hardened WooCommerce theme.

Shop, product, cart, checkout, account, blog, search, 404 — all real
WordPress/WooCommerce data, no placeholder content.

WooCommerce compatibility hooks restored (shop loop, single product
summary, cart, reviews) so third-party WooCommerce extensions integrate
correctly.

Real SEO fundamentals in place: correct <h1> hierarchy sitewide, meta
description, Open Graph / Twitter Card tags, Organization/WebSite +
BreadcrumbList structured data, real Product structured data.

Performance pass done: dead CSS removed, WordPress <head> bloat
stripped, Bootstrap trimmed to only the modules actually used.

No build step. assets/css/main.css and assets/css/bootstrap-custom.css
are plain hand-maintained CSS — the SCSS toolchain was removed entirely.

====================================================

Phase 2 — Core Plugin, Settings, Header/Footer Builder

Ecombon Core will NOT be built from scratch.

It will be built on top of an existing, real plugin: NoorPDP.

NoorPDP today (c:\wamp64\www\noorblocks\wp-content\plugins\noorpdp):

29 real registered Gutenberg blocks, auto-discovered from block.json
(includes/Blocks/Manager.php) — generic content blocks (container,
heading, button, tabs, accordion, feature-cards, icon-list,
comparison-table, trust-badges, urgency, hero) plus WooCommerce
single-product-page blocks (product-add-to-cart, product-gallery-carousel,
product-tabs, related-products, sticky-add-to-cart, and more).

Modern build tooling already in place — @wordpress/scripts, src/ → build/.

PSR-4-style autoloader, NoorPDP\ namespace.

A classic (non-React) admin settings page already exists
(includes/Admin/Dashboard.php) — block enable/disable, default layout.

A real Layouts system (includes/Layouts/: Post_Type, Resolver, Meta_Box,
Template_Override, Duplicate, Preview) — currently scoped to the single
product page only.

Mature engineering: CI, PHPCS/WPCS, git history, WordPress.org-track
docs, a companion noorpdp-pro add-on with Freemius licensing wired in.

Currently product-page-only in scope — not yet general-purpose site
sections.

--- Three concrete Phase 2 workstreams ---

1. Modern settings panel

Replace/extend NoorPDP's classic Admin\Dashboard.php with a real React
(@wordpress/components) admin UI.

Scope: the settings the Ecombon theme already anticipates via its own
filters and has zero UI for today —

ecombon_contact_phone / ecombon_contact_email / ecombon_contact_address
(footer.php, mobile-menu.php)

ecombon_social_links (footer.php, also feeds SEO.php's Organization
JSON-LD sameAs)

Plus new: Google Fonts selection (explicitly deferred to "theme
settings panel" during theme build), and brand colors (the theme's
:root CSS custom properties in assets/css/main.css, currently
hardcoded).

The theme's own filter-consuming code stays untouched — Core Plugin
becomes the real settings source behind those existing filters.

2. Header Builder + Footer Builder

New capability. Modeled on NoorPDP's existing Layouts pattern
(Post_Type + Resolver + Meta_Box + Template_Override), generalized to
header/footer placement instead of single-product content only.

3. General-purpose blocks

Extend the block library beyond product-page-only blocks to real
homepage/site-building sections, reusing the same auto-registration
mechanism (Blocks/Manager.php).

--- Open decision, not yet made ---

How NoorPDP physically joins the Ecombon platform — copied/renamed into
the Ecombon site's wp-content/plugins/, or kept as a separate dependency
plugin. Decide this before starting implementation.