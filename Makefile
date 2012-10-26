docs:
	mkdir -p "docs"
	apigen \
	--source ./../common/ \
	--source ./ \
	--destination docs/ --title ICanBoogie/Prototype \
	--exclude "*/build/*" \
	--exclude "*/tests/*" \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon

clean:
	rm -fR docs