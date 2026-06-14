# Laravel 13 & PHP 8.5 Knowledge Base

This document stores critical findings and documentation for Laravel 13, which was released in March 2026. It serves as an authoritative reference for AI agents working in this environment.

## 1. Core Architectural Changes

### PHP 8.3/8.4+ Primes
- **Minimum PHP 8.3**: Laravel 13 requires PHP 8.3 or higher.
- **Property Hooks**: Laravel 13 natively supports PHP 8.4 property hooks for declarative validation and formatting on model attributes.
- **Typed Class Constants**: Used extensively within the framework core.

### Native AI SDK
- **Laravel AI**: A first-party SDK (`laravel/ai`) providing a unified interface for text generation, image synthesis, audio processing, and tool-calling.
- **Agent Integration**: Features built-in detection and optimization for AI agents via `laravel/pao`.

## 2. Database & Eloquent

### Semantic Search
- **Vector Support**: Native similarity search via `whereVectorSimilarTo('column', 'search query')`.
- **Requirements**: Requires PostgreSQL with `pgvector` or compatible drivers.

### Model Attributes
- **Declarative Configuration**: Use PHP Attributes for model setup:
    - `#[Table('name')]`
    - `#[Fillable(['a', 'b'])]`
    - `#[Hidden(['password'])]`
    - `#[Primary('uuid')]`
- **Legacy Property Support**: Traditional `protected $fillable` etc. still work as a fallback.

## 3. Testing Infrastructure

### Agent-Optimized Output (PAO)
- **laravel/pao**: Automatically detects AI agents and switches terminal output to structured JSON.
- **Efficiency**: Reduces token consumption by up to 99% for large test suites.

### Test Isolation
- **Automatic Factory Resets**: `Str` and other global factories now reset between every test to prevent state leakage.
- **Parallel Testing**: Improved process token management and automatic test database creation.

### parent::setUp()
- **Mandatory**: Must be called at the beginning of the `setUp()` method in test classes.
- **Function**: Boots the application, initializes traits, and sets up the service container.

## 4. Middleware & Security

### Origin-Aware CSRF
- **PreventRequestForgery**: Formalized middleware with enhanced `Origin` header verification for cross-site state protection.

### JSON:API
- **First-party support**: Native resources for compliant JSON:API responses.
