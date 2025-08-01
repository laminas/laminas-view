# Run `make` (no arguments) to get a short description of what is available
# within this `Makefile`.

MKDOCS_IMAGE_ID := $(shell docker images -q laminas/mkdocs | xargs)
MDLINT_FILE = https://raw.githubusercontent.com/laminas/laminas-continuous-integration-action/refs/heads/1.43.x/setup/markdownlint/markdownlint.json

help: ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
.PHONY: help

documentation-theme: ## fetch the documentation theme repo
	git clone git@github.com:laminas/documentation-theme.git

build-mkdocs-image: documentation-theme ## Build the mkdocs image with necessary dependencies for building the docs
	$(if ${MKDOCS_IMAGE_ID}, $(info Image already built), cd documentation-theme/builder && docker build -t laminas/mkdocs .)
.PHONY: build-mkdocs-image

docs: build-mkdocs-image ## build the docs using a Docker container
	docker run -it -w /app -v ${PWD}:/app --rm laminas/mkdocs ./documentation-theme/build.sh -u https://www.example.com
	$(info ${PWD}/docs/html/index.html)
.PHONY: docs

docs-lint: .markdownlint.json ## Lint documentation
	docker run -it -w /app -v ${PWD}:/app --rm davidanson/markdownlint-cli2 "docs/**/*.md" "README.md"
.PHONY: docs-lint

.markdownlint.json: ## Fetch the most recent settings for Markdown lint
	curl -o .markdownlint.json ${MDLINT_FILE}

install: install-tools ## Install PHP dependencies
	composer install
.PHONY: install

install-tools: ## Install standalone dev tools
	cd tools/crc && composer install
	cd tools/infection && composer install
	cd tools/rector && composer install
.PHONY: install-tools

update: ## Update PHP dependencies
	composer update
.PHONY: update

bump: bump-tools ## Bump dev dependencies and update
	composer update && composer bump -D && composer update
.PHONY: bump

bump-tools: ## Bump and update standalone dev tools
	cd tools/crc && composer update && composer bump -D && composer update
	cd tools/infection && composer update && composer bump && composer update
	cd tools/rector && composer update && composer bump && composer update
.PHONY: bump-tools

clean: ## Clear out caches and documentation assets
	rm -rf documentation-theme
	rm -rf docs/html
	$(if ${MKDOCS_IMAGE_ID}, docker image rm laminas/mkdocs, echo "Skip image removal" )
	rm -rf .phpunit.cache
	rm -f .phpcs-cache
	vendor/bin/psalm --clear-cache
	rm -f .markdownlint.json
.PHONY: clean

sa: ## Run static analysis checks
	vendor/bin/psalm --no-cache
.PHONY: sa

cs: ## Run coding standards checks
	vendor/bin/phpcs
.PHONY: cs

test: ## Run unit tests
	vendor/bin/phpunit
.PHONY: test

mutants: ## Run mutation tests
	tools/infection/vendor/bin/roave-infection-static-analysis-plugin \
 		--configuration=.infection.json5.dist \
 		--psalm-config=psalm.xml.dist
.PHONY: mutants

composer-validate: ## Validate composer.json and lock
	composer validate --strict
.PHONY: composer-validate

composer-require-checker: ## Check for symbols from un-declared dependencies
	tools/crc/vendor/bin/composer-require-checker check --config-file=tools/crc/config.json
.PHONY: composer-require-checker

qa: composer-validate cs sa test composer-require-checker docs-lint rector ## Run all QA Checks
.PHONY: qa

rector: ## Run Rector and show the diff
	tools/rector/vendor/bin/rector process --dry-run -vv -c tools/rector/rector.php
.PHONY: rector

rector-ci: ## Run Rector and show the diff in GitHub format for CI
	tools/rector/vendor/bin/rector process --dry-run --output-format=github -vv -c tools/rector/rector.php
.PHONY: rector

rector-fix: ## Apply Rector changes
	tools/rector/vendor/bin/rector process -c tools/rector/rector.php
.PHONY: rector-fix
