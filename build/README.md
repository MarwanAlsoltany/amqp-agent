# Note!

**Do not delete the files and directories in this directory!**

The file structure above is needed in order to run AMQP Agent composer scripts.
Some scripts expect a file to be created. They will throw an error if the file does not exist.
Files: `phpcs/index.xml`, `phpmd/index.xml`, and `phploc/index.html`.

Please not that these files shouldn't be committed. If a rebuild for the documenation is attempted they will get filled again, delete their contants manually before commiting.