help:
	@echo "Available targets:"
	@echo "  test        Run all tests with pretty output (php artisan test)"
	@echo "  test-watch  Watch for file changes and run tests, suppressing deprecation warnings (phpunit-watcher)"

# Run Laravel's pretty test runner
.PHONY: test
test:
	php artisan test

# Watch for file changes and run tests, suppressing deprecation warnings
.PHONY: test-watch
test-watch:
	php -d error_reporting=E_ALL^E_DEPRECATED vendor/bin/phpunit-watcher watch 