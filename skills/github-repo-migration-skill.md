---
description: Safely migrate an authorized project from an old GitHub
  repository/account to a new repository/account on Windows using
  PowerShell and SSH aliases.
name: github-repo-migration
---

# GitHub Repository Migration Skill (Windows PowerShell)

## Purpose

Use this workflow when moving a project from an old GitHub repository to
a new repository, possibly under a different GitHub account, on the same
Windows computer.

**Important:** This workflow is for projects you own or are explicitly
authorized to copy, modify, and publish in the destination repository. A
new Git repository does not change code ownership, licensing,
confidentiality, or attribution. Never use history removal or
contributor changes to disguise where code came from. Keep required
copyright, license, and third-party notices.

## 1. Choose the migration mode

Choose one mode before changing anything.

### Mode A --- Keep Git history (recommended when history/provenance matters)

Use this when the old commit history should remain available.

``` powershell
Set-Location "D:\path\to\project"

git status
git remote -v
git branch --show-current
git fetch --all --prune
```

Make a backup before proceeding. Then point the existing repository at
the new destination:

``` powershell
git remote set-url origin "git@github-personal:NEW-OWNER/NEW-REPO.git"
git push -u origin HEAD
```

If the destination already contains an independent README/license
commit, stop and inspect both histories before merging. Do not blindly
force-push.

### Mode B --- Start a new Git history

Use this only when a fresh history is genuinely required and you are
authorized to publish the project in this form. This removes the local
repository's Git history and metadata; it does **not** remove company
ownership, license obligations, secrets, or provenance from the source
files.

1.  Make a full backup of the project folder.
2.  Confirm the destination repository is empty, or understand and
    intentionally preserve/replace its existing contents.
3.  Review the project for company-confidential material, credentials,
    personal data, proprietary assets, and third-party components.
    Remove only material you are authorized to remove or replace. Do not
    remove copyright/license notices to conceal provenance.
4.  From the project root, inspect the current Git directory and then
    remove it only after confirming the backup:

``` powershell
Set-Location "D:\path\to\project"
Get-Location
Get-ChildItem -Force
git status
```

If the folder contains the expected project and `.git` is the repository
metadata directory:

``` powershell
Remove-Item -Recurse -Force .git
git init
git branch -M main
```

Review what will be committed before staging:

``` powershell
git status --short
```

Add an appropriate `.gitignore` before `git add`. Do not commit secrets,
local environment files, dependencies, caches, logs, or generated files
unless the project specifically requires them.

``` powershell
git add .
git status
git diff --cached --stat
git diff --cached --name-only
```

Inspect staged files carefully. Then commit:

``` powershell
git commit -m "Initial commit"
```

Connect the destination and push:

``` powershell
git remote add origin "git@github-personal:NEW-OWNER/NEW-REPO.git"
git remote -v
git push -u origin main
```

If push is rejected because the destination has commits, stop. Inspect
the remote first:

``` powershell
git fetch origin
git log --oneline --decorate --all --graph
```

Do not use `--force` or `--force-with-lease` unless you own/administer
the destination, have confirmed exactly what will be overwritten, and
have a backup. Prefer an empty destination repository for a
fresh-history migration.

## 2. Configure multiple GitHub accounts with SSH

Create one SSH key per GitHub account. Example names:

-   Personal: `~/.ssh/id_ed25519_personal`
-   Work: `~/.ssh/id_ed25519_work`

Create the `.ssh` folder if needed:

``` powershell
New-Item -ItemType Directory -Force -Path "$HOME\.ssh"
```

Generate a key only if it does not already exist:

``` powershell
ssh-keygen -t ed25519 -C "PERSONAL_EMAIL" -f "$HOME\.ssh\id_ed25519_personal"
ssh-keygen -t ed25519 -C "WORK_EMAIL" -f "$HOME\.ssh\id_ed25519_work"
```

Add each `.pub` public key to the matching GitHub account under
**Settings → SSH and GPG keys**. Never share or upload the private key
(the file without `.pub`).

Create or edit `$HOME\.ssh\config`:

``` sshconfig
Host github-personal
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519_personal
    IdentitiesOnly yes

Host github-work
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519_work
    IdentitiesOnly yes
```

Test each account:

``` powershell
ssh -T git@github-personal
ssh -T git@github-work
```

The greeting should show the expected GitHub username. The message that
GitHub does not provide shell access is normal.

Use the matching alias in repository URLs:

``` powershell
# Personal account
git clone git@github-personal:OWNER/REPO.git

# Work account
git clone git@github-work:OWNER/REPO.git
```

For an existing local repository, switch its remote:

``` powershell
git remote set-url origin "git@github-personal:OWNER/REPO.git"
# or
git remote set-url origin "git@github-work:OWNER/REPO.git"
```

Check the remote:

``` powershell
git remote -v
```

**Changing the remote does not change commit author identity.** SSH
selects the GitHub account used for authentication; `user.name` and
`user.email` are commit metadata.

## 3. Set commit identity per project

Run these commands from inside the project's Git repository:

``` powershell
git config user.name "Your Name"
git config user.email "your-verified-email@example.com"
```

Check:

``` powershell
git config --local --list
```

If you are outside a Git repository, `git config user.name` without
`--global` will fail. For a machine-wide default only:

``` powershell
git config --global user.name "Default Name"
git config --global user.email "default@example.com"
```

Prefer per-project identity when using personal and work accounts on the
same computer. Use an email verified on the relevant GitHub account if
you want commits attributed to that account.

## 4. Common PowerShell commands

  ---------------------------------------------------------------------------------------------------------
  Task                                PowerShell
  ----------------------------------- ---------------------------------------------------------------------
  Go to D drive root                  `Set-Location D:\`

  Enter a project folder              `Set-Location "D:\path\to\project"`

  List files including hidden         `Get-ChildItem -Force`

  Show current folder                 `Get-Location`

  Read SSH config                     `Get-Content "$HOME\.ssh\config"`

  Open SSH config                     `notepad "$HOME\.ssh\config"`

  Copy public key                     `Get-Content "$HOME\.ssh\id_ed25519_personal.pub" \| Set-Clipboard`
  ---------------------------------------------------------------------------------------------------------

PowerShell's `ls` is an alias for `Get-ChildItem`; it does not support
Unix `ls -la`. Use `Get-ChildItem -Force`.

## 5. Pre-push checklist

Before every migration/push, confirm:

-   [ ] You own the project or have explicit permission to publish it in
    the destination.
-   [ ] Required copyright, license, attribution, and third-party
    notices are retained.
-   [ ] No company-confidential files, credentials, tokens, private
    keys, `.env` files, or personal data are staged.
-   [ ] Third-party plugins/themes/assets (including paid products) are
    licensed for this use and redistribution.
-   [ ] `.gitignore` is appropriate for the project.
-   [ ] `git status` and `git diff --cached --name-only` show only
    intended files.
-   [ ] `git remote -v` points to the intended owner/repository and SSH
    alias.
-   [ ] `ssh -T git@github-personal` or `ssh -T git@github-work`
    authenticates as the intended account.
-   [ ] The destination's existing commits have been inspected before
    any merge or force push.

## 6. Troubleshooting

### `Could not resolve hostname github-work`

The SSH alias is missing or misspelled. Check:

``` powershell
Get-Content "$HOME\.ssh\config"
```

Ensure the file is named `config` (not `config.txt`) and contains a
matching `Host github-work` block.

### `Repository not found`

Common causes: - The owner/repository path is incorrect. - The SSH alias
authenticates as the wrong GitHub account. - The authenticated account
has not been granted access to the private repository.

Check the account:

``` powershell
ssh -T git@github-work
```

Then verify repository access and the remote:

``` powershell
git remote -v
```

### `origin does not appear to be a git repository`

The repository has no `origin` remote. Add it:

``` powershell
git remote add origin "git@github-personal:OWNER/REPO.git"
```

### `rejected (fetch first)`

The remote has commits not present locally. Fetch and inspect before
deciding how to integrate:

``` powershell
git fetch origin
git log --graph --oneline --decorate --all
```

Do not blindly force-push.

### `LF will be replaced by CRLF`

This is usually a line-ending warning on Windows, not a failed
operation. Check whether the command completed with:

``` powershell
git status
```

Do not change line-ending settings unless the project has a deliberate
`.gitattributes` policy.

------------------------------------------------------------------------

## Recommended repeatable workflow

For each authorized project:

1.  Back up the folder.
2.  Decide whether to preserve history (Mode A) or create a new history
    (Mode B).
3.  Audit source files, secrets, licensing, and attribution.
4.  Confirm the correct SSH alias and destination repository.
5.  Set the project's commit identity.
6.  Review staged files.
7.  Push and verify the GitHub repository.
