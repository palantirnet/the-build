### phing-relative-symlinks.patch

Source: [https://github.com/phingofficial/phing/pull/695](https://github.com/phingofficial/phing/pull/695)

This patch updates Phing's SymlinkTask to allow creating relative symlinks, using the syntax:

```
<symlink link="path/to/destination" target="path/to/target" relative="true" />
```
