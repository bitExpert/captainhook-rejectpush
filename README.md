# bitexpert/captainhook-rejectpush

This package provides an action for [Captain Hook](https://github.com/CaptainHookPhp/captainhook) 
which will reject a push to a remote when configured commit Ids are found in the Git history.

[![Build Status](https://travis-ci.org/bitExpert/captainhook-rejectpush.svg?branch=master)](https://travis-ci.org/bitExpert/captainhook-rejectpush)
[![Coverage Status](https://coveralls.io/repos/github/bitExpert/captainhook-rejectpush/badge.svg?branch=master)](https://coveralls.io/github/bitExpert/captainhook-rejectpush?branch=master)

## Installation

The preferred way of installing `bitexpert/captainhook-rejectpush` is through Composer.
You can add `bitexpert/captainhook-rejectpush` as a dev dependency, as follows:

```
composer.phar require --dev bitexpert/captainhook-rejectpush
```

## Usage

Add the following code to your `captainhook.json` configuration file:

```
{
  "pre-push": {
    "enabled": true,
    "actions": [
      {
        "action": "\\bitExpert\\CaptainHook\\RejectPush\\RejectPushAction",
        "options": {
            "my-origin": [
                "cc9d54f"
            ],
            "other-remote": [
                "41ce954"
            ]
        }
      }
    ]
  }
}
```

[Captain Hook](https://github.com/CaptainHookPhp/captainhook) will now check
on every push if one of the defined commit Ids is part of the push. If so, it
will cancel the push.

## Contribute

Please feel free to fork and extend existing or add new features and send a pull request with your changes! To establish a consistent code quality, please provide unit tests for all your changes and adapt the documentation.

## Want To Contribute?

If you feel that you have something to share, then we’d love to have you.
Check out [the contributing guide](CONTRIBUTING.md) to find out how, as well as what we expect from you.

## License

Captain Hook Reject Push Action is released under the Apache 2.0 license.