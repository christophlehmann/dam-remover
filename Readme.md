TYPO3 Extension DAM Remover

This extension helps you to remove the extensions dam and dam_ttcontent with currently two Extbase commands.

1)  imagerelations:convert

	The extension dam_ttcontent provides an additional image field in content elements. If you don't use it, skip this
	command.

	This command searches for that images and attaches them to the normal image field. Image files are copied to
	upload/pics.

2)  mediatags:convert

	DAM provides the mediatag functionality. If you don't use this feature, skip this command. DAM replaces file links
	in RTE-fields with media links which contain a dam id instead of the filename (like the link tag does).

	This command searches for media tags and converts them to traditional file links. Currently only tt_contents field
	bodytext is supported.


What you have to do is:

1)  You may check the db integrity and fix errors
2)  Make a backup of your database ( or only tables tt_content and tx_dam_mm_ref )
3)  Run imagerelations:convert and remove the extension dam_ttcontent.
4)  Run medialinks:convert

	Beware: There can be more fields containing media tags, eg. search for "<media" in the whole database.

5)  Remove dam and check your frontend!


I would be happy about comments on this!