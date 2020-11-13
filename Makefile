.Phony: update

update:
	@docker run -it -w /data -v ${PWD}:/data:delegated -v ~/.composer:/root/.composer:delegated --entrypoint composer --rm registry.gitlab.com/grahamcampbell/php:7.4-base update

test: phpunit

phpunit:
	@docker run -it -w /data -v ${PWD}:/data:delegated -v ~/.composer:/root/.composer:delegated --entrypoint vendor/bin/phpunit --rm registry.gitlab.com/grahamcampbell/php:7.4-base

phpunit-migrate:
	@docker run -it -w /data -v ${PWD}:/data:delegated -v ~/.composer:/root/.composer:delegated --entrypoint vendor/bin/phpunit --rm registry.gitlab.com/grahamcampbell/php:7.4-base --migrate-configuration
