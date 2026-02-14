.PHONY: build install uninstall

build:
	docker build -t cclsp-phpactor:latest .

install: build
	./install.sh

uninstall:
	./uninstall.sh
