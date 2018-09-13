# Building an Artifact

This file describes a generalized artifact build process. This process is implemented in [tasks/artifact.xml](../tasks/artifact.xml), and usage is described in the [properties documentation](properties.md).

### Definitions

* **build artifact:** a copy of a project codebase at a particular point in time, with all dependencies and configuration files required to run the site on a staging or production environment.
* **artifact repository:** a git repository where build artifacts are committed.
* **development repository:** a git repository where development happens and custom code and configuration are committed.
* **integrated artifact:** when a project has all project dependencies and configuration files committed to the development repository.

### Hosting and artifacts

* For *Acquia* and *Pantheon*, the artifact is checked into the host's git repository
* For *Platform.sh*, the artifact is built and managed by Platform.sh

### Artifact creation tools

Different tools can be used to create a build artifact, including:

* [palantirnet/the-build](https://github.com/palantirnet/the-build)
* [acquia/blt](https://github.com/acquia/blt)
* Custom scripts

### Integrated artifacts

When all project dependencies and configuration files are committed to the development repository, we call it an "integrated artifact". When using an integrated artifact, consider:

* The project may have merge conflicts in dependency code during development
* Development dependencies will be deployed to production
* Settings and configuration files will need to have PHP logic to manage varying configurations per environment. Because of Drupal's permissions on the `settings.php` file, this can prevent easily switching branches when this file is modified.
* The "development repository" === the "artifact repository". This relationship can be more straightforward to understand for developers unfamiliar with composer and build processes
* It's easy to modify core and contrib code that should not be within our area of responsibility

### Creating an artifact

This section describes the requirements for creating an artifact, but doesn't dictate the tool itself.

Contents:

* The foundation of the artifact is the **`composer.json` file, plus any files checked in to the development repository.**
* **Run `composer install --no-interaction --no-dev`.** Note that after running this, development requirements like phing, Drupal coder, Drush, or Drupal console won't be available. This is why artifacts are frequently built separately from the current working directory, e.g. in a subdirectory called `artifacts/acquia/` that is excluded from git.
* **Recursively remove `.git` directories** (below the root directory) from the artifact. Composer will install some dependencies from source, which means that it will check out git repositories for you. If these `.git` subdirectories are not removed, git will treat them as sub-repositories and the files themselves will not be checked into the artifact, which will mean that the files are not available on the destination environments.
* **Compile `settings.php` with environment-specific values.** Alternatively, you can take the "integrated artifact" approach and use a single `settings.php` file containing logic for each separate environment.
* **Compile `services.yml` with environment-specific values.** If you're using a single `settings.php` file, you can also use environment logic there to switch between several different, environment-specific `services.yml` files.
* **Use a different `.gitignore` file** for the artifact. The development repository should exclude the `vendor/` directory, the `settings.php` file, and a few other resources; the artifact repository should _include_ the `vendor/` directory, the `settings.php` file, and a few other resources. _Both_ repositories should exclude the Drupal public, private, and temporary files directories.

Commits:

* Artifact commit messages should reference the commit id on the development repository. They should also note whether the repository was "dirty" (had local changes) when the artifact was built.
* Artifact commits should be tagged with a corresponding (but not identical) tag to the development repository, if available. e.g. if the development repository is tagged `1.0.2`, the artifact repository may be tagged `artifact-1.0.2`

Safeguards:

* Artifact builds shouldn't change the current working directory
* Users should be able to review manual builds before pushing changes
