# PlayerTitles Plugin

## Overview

The PlayerTitles plugin for PocketMine-MP allows players to view and manage their titles through a user-friendly UI. Server admins can create new titles either through the configuration file or by using in-game commands. Each player's currently equipped title is stored in an SQLite database, ensuring persistent title management.

## Features

- **UI for Title Management**: Players can view their unlocked and locked titles via an intuitive UI.
- **Title Creation**: Admins can create new titles through the configuration file or by using in-game commands.
- **SQLite Storage**: The currently equipped title for each player is stored in an SQLite database.
- **Integration with RankSystem**: Requires the RankSystem plugin for title management.

## Installation

1. Download the PlayerTitles plugin.
2. Place the plugin `.phar` file into the `plugins` directory of your PocketMine-MP server.
3. Restart your server to load the plugin.

## Configuration

The plugin uses a `config.yml` file for configuration. You can define titles and their display formats in this file. Example configuration:

```yaml
# (!) DO NOT CHANGE (!)
version: 1

# (!) NOTICE (!)
# You can use '&' for color codes.
# use {title} in RankSystems config.yml to replace it with player's title
# e.g: "{chat_ranks_prefix}{chat_name-color}{display_name}{title}{chat_format}{message}"
#
# Permissions will be automatically applied. e.g:
# playertitles.title.example

# Configurable information for the plugin.
settings:
  titles-per-page: 5 # How many titles to show per page
  message:
    - "&r&aSelected Title: '{title}&a'"
  no-title-perm: "&r&cYou don't have access to this title!"

titles:
  example: # Identifier 
    display: "&r&8[&cExample&8]&r"
  test:
    display: "&r&5Test&r"

# If the command exists, e.g "titles", the plugin will automatically unregister the vanilla command.
# IF renaming it to a vanilla command, make sure you wont need to use the command as it will no longer be available unless the
# command name is changed.
commands:
  titles:
    name: "title" # Main command
    description: "Open titles menu"
    aliases:
      - titles

# Configurable information for the forms available by the plugin.
forms:
  titles:
    name: "&r&8Titles"
    back: "&r&8Previous Page"
    next: "&r&8Next Page"
    

# Forms
forms:
  titles:
    name: "&r&8Titles"
    back: "&r&8Previous Page"
    next: "&r&8Next Page"
