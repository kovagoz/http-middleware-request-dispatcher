.PHONY: test
test: | vendor
	docker run --rm -it -v $(PWD):/host -w /host php:7.4-alpine ./vendor/bin/phpunit test

vendor:
	docker run --rm -it -v $(PWD):/host -w /host composer:2 install

.PHONY: autoloader
autoloader:
	docker run --rm -it -v $(PWD):/host -w /host composer:2 dump-autoload
