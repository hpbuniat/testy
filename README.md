Testy - a continuous test-runner
=====

**Testy** was created to assist in TDD, as well as to remind a lazy developer to use tests.
Testy monitors a directory for changed files and will run the appropriate test.

Testy might be used, to execute an arbitrary command or application, e.g. php-codesniffer, php-hint, etc.

Project-Configuration
-----

Example w/o File to Test-Mapping
<pre>
"testy": {
    "path": "~/workspace/testy",
    "test": "phpunit",
    "test_dir": "~/workspace/testy;",
    "syntax": "php -l $file",
    "find": "*.php"
}
</pre>

Example with File to Test-Mapping
<pre>
"testy": {
    "path": "~/workspace/testy",
    "test": "phpunit $file {Testy|Tests} {.php|Test.php}",
    "test_dir": "~/workspace/testy;",
    "syntax": "php -l $file",
    "find": "*.php"
}
</pre>

Options
-----
- path:     The path that is checked for changed files
- find:     The find-pattern that is used to find changed files
- syntax:   Command to do a syntax-check (skips testing on error)
- test_dir: The dir to cd in, before executing the test-command (which is also checked for changes)
- repeat: Repeat the test-command without the specific file (which is replaced by ''), default: true
- test:     The test to execute on changed files
    - placeholders:
    - $file       Each file that changed
    - $project    The projects name
    - $time       The current timestmap
    - $mtime      The modification's timestamp

Notifiers
-----
Testy supports several ways to notify the developer about the result of the executed command:

- Stdout, which simply prints the output
- File
- Dbus
- Growl, which works well with snarl on windows
- Libnotify, which used notify-send

File-to-Test-Mapping
-----
Use the {Search|Replace}-Pattern, as often as needed, to map the Source-File to it's test.
If all Search-Patterns are found within the changed-files path, it is assumed that this is a test file.

Notes
-----
The dbus-notifier is based on the php dbus-extension (http://pecl.php.net/package/DBus) by Derick Rethans
