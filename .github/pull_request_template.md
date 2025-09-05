# [UHF-0000](https://helsinkisolutionoffice.atlassian.net/browse/UHF-0000)
<!-- What problem does this solve? -->

## What was done
<!-- Describe what was done -->

* This thing was fixed

## How to install
* Make sure your instance is up and running on latest dev branch.
    * `git pull origin dev`
    * `make fresh`
* Update the Helfi API Base module
    * `composer require drupal/helfi_api_base:dev-UHF-0000_insert_correct_branch`
* Run `make drush-updb drush-cr`

## How to test
<!-- Describe steps how to test the features, add as many steps as you want to be tested -->

* [ ] 
* [ ] Check that code follows our standards

<!-- Check list for the developer. Did you update/add/check the -->
<!-- * documentation -->
<!-- * translations -->
<!-- * coding standards -->

## Other PRs
<!-- For example a related PR in another repository -->

* 
