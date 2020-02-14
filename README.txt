Embed Block is tiny module that renders a block into formatted text by using a
format filter.

On order to embed a block in text, you should add the following placeholder:
`{block:PLUGIN_ID}`, where `PLUGIN_ID` is the block plugin identifier. The block
access is checked against the current user and if the access is denied, the
filter replaces the placeholder with an empty string (""). If the specified
plugin ID doesn't exist, the placeholder is kept unchanged.


