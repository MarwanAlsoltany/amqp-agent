# Note!

**Do not delete the files and directories in this directory!**

The file structure above is needed in order to run AMQP Agent composer scripts.
Some scripts expect a file to be created. They will throw an error if the file does not exist.
Files: `phpcs/index.xml`, `phpmd/index.xml`, and `phploc/index.html`.

Please not that these files shouldn't be committed. If a rebuild for the documenation is attempted they will get filled again, **delete their contants manually before commiting**.


**Post build requirements**

PhpDox will include an absolute path to the files in source view when building the docs for example `C:/Users/SomeUser/SomeFolder/SomeOtherFolder/ProjectDirectory/src/Namespace/SomeClass.php`.

Using IDE find `C:/Users/SomeUser/SomeFolder/SomeOtherFolder/` in directories `docs` and `build` and replace it with an empty string, so that it looks like `ProjectDirectory/src/Namespace/SomeClass.php` without exposing private information about the developer's machine.