# Standardizing issue labels

This folder contains a json file that manages the issue labels for the organization.

It is applied to our repos via [org-labels](https://github.com/repo-utils/org-labels).

For this to work correctly, you need to use the [Fork by RedFlagTeam](https://github.com/RedFlagTeam/org-labels). 
To install:

```bash
git clone git@github.com:RedFlagTeam/org-labels.git
cd org-labels
npm install
npm link
```

Then, to apply all labels in this style-guide and delete all labels that are not defined here, run this command:

```bash
org-labels -d standardize viison style-guide
```
