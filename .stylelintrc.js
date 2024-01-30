
module.exports = {
    "extends": "stylelint-config-standard",
    "ignoreFiles": [
        "lib/**/*",
        "vendor/**/*"
    ],
    "rules": {
        "selector-class-pattern": null, // DISABLE: Expected class selector to be kebab-case
    },
};
