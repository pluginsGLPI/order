include ../../PluginsMakefile.mk

##—— Symfony AI Platform ————————————————————————————————————————————————————————
sync-symfony-ai: ## Download/update the vendored Symfony AI Platform source code
	@bash tools/update-symfony-ai.sh
	@$(PLUGIN) composer dump-autoload
.PHONY: sync-symfony-ai

apply-patches: ## Apply local patches from .patches/ to the vendored Symfony AI code
	@bash tools/apply-patches.sh
.PHONY: apply-patches

##—— Migrations ——————————————————————————————————————————————————————————————————
make-migration: ## Scaffold a timestamped migration (make make-migration c='"add foo column"')
	@$(eval c ?=)
	@$(CONSOLE) plugins:glpiai:make-migration $(c)
.PHONY: make-migration

migrate: ## Run pending migrations (add c='--down' to roll back one step)
	@$(eval c ?=)
	@$(CONSOLE) plugins:glpiai:migrate $(c)
.PHONY: migrate

test-migrate: ## Migrate test plugin env to latest version
	@$(eval c ?=)
	@$(CONSOLE) plugins:glpiai:migrate --env=testing $(c)
.PHONY: make-test-migrate

test-downgrade: ## Downgred test plugin env to latest version
	@$(CONSOLE) plugins:glpiai:migrate --env=testing --down
.PHONY: make-test-migrate

##—— RAG / Embeddings —————————————————————————————————————————————————————————
process-vector-queue: ## Drain the RAG vector indexing queue, looping until empty (add c='--time-limit=30')
	@$(eval c ?=)
	@$(CONSOLE) plugins:glpiai:process-vector-queue $(c)
.PHONY: process-vector-queue
