# Contributing to ELKCMS

Thank you for considering contributing to ELKCMS!

## Development Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`php artisan test`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Coding Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

## Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/ContentModelTest.php
```

## Code Style

We use PHP CS Fixer to maintain consistent code style:

```bash
# Check code style
./vendor/bin/php-cs-fixer fix --dry-run

# Fix code style
./vendor/bin/php-cs-fixer fix
```

## Docker Development

Use Docker for a consistent development environment:

```bash
# Start containers
docker-compose up -d

# Run tests in container
docker-compose exec app php artisan test

# Access application container
docker-compose exec app bash
```

## Pull Request Guidelines

- PRs should be focused on a single feature or fix
- Include tests for new functionality
- Update documentation if you're changing functionality
- Ensure all tests pass before submitting
- Keep PRs small and focused for easier review

## Code Review Process

All submissions require review. We use GitHub pull requests for this purpose.

## Reporting Bugs

Report bugs via [GitHub Issues](https://github.com/kokiddp/elkcms/issues).

When reporting a bug, include:

- Your PHP and Laravel versions
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Any error messages or stack traces

## Feature Requests

Feature requests are welcome! Please discuss them in [GitHub Discussions](https://github.com/kokiddp/elkcms/discussions) before submitting a PR.

## Community

- Report bugs via [GitHub Issues](https://github.com/kokiddp/elkcms/issues)
- Discuss features in [GitHub Discussions](https://github.com/kokiddp/elkcms/discussions)
- Follow our [Code of Conduct](CODE_OF_CONDUCT.md)

## License

By contributing to ELKCMS, you agree that your contributions will be licensed under the MIT License.
