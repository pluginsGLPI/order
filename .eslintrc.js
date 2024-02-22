
module.exports = {
    "root": true,
    "ignorePatterns": [
        "/node_modules/*",
        "/vendor/*",
    ],
    "env": {
        "browser": true,
        "es6": true,
        "jquery": true,
    },
    "extends": "eslint:recommended",
    "globals": {
        "CFG_GLPI": true,
        "GLPI_PLUGINS_PATH": true,
        "__": true,
        "_n": true,
        "_x": true,
        "_nx": true
    },
    "parserOptions": {
        "ecmaVersion": 8,
    },
    "plugins": [
        "@stylistic/js",
    ],
    "rules": {
        "no-console": ["error", {"allow": ["warn", "error"]}],
        "no-unused-vars": ["error", {"vars": "local"}],
        "@stylistic/js/eol-last": ["error", "always"],
        "@stylistic/js/indent": ["error", 4],
        "@stylistic/js/linebreak-style": ["error", "unix"],
        "@stylistic/js/semi": ["error", "always"],
    },
    "overrides": [
        {
            "files": [".eslintrc.js"],
            "env": {
                "node": true
            }
        },
    ],
};
