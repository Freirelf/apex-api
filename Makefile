SHELL := /bin/bash
COMPOSE := ./vendor/bin/sail

up:
	@$(COMPOSE) up -d

down:
	@$(COMPOSE) down

restart:
	@$(COMPOSE) down && $(COMPOSE) up -d

migrate:
	@$(COMPOSE) artisan migrate

seed:
	@$(COMPOSE) artisan db:seed

tinker:
	@$(COMPOSE) artisan tinker

logs:
	@$(COMPOSE) logs -f
