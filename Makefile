.DEFAULT_GOAL := help

MAKEFILES_FOLDER ?= $(PWD)/makefiles

include $(MAKEFILES_FOLDER)/variables
include $(MAKEFILES_FOLDER)/docker_compose
include $(MAKEFILES_FOLDER)/dependencies
include $(MAKEFILES_FOLDER)/database
include $(MAKEFILES_FOLDER)/development
include $(MAKEFILES_FOLDER)/symfony
include $(MAKEFILES_FOLDER)/tests
include $(MAKEFILES_FOLDER)/terraform

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m\033[0m\n"} /^([0-9a-zA-Z_-]+:.*)?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST) | sed "s/##//g" | sed "/#[><]\s.*\s#/d"
