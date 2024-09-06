-- #!sqlite
-- # { players
-- #  { initialize
CREATE TABLE IF NOT EXISTS players (
    uuid VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16),
    title TEXT
    );
-- #  }

-- #  { select
SELECT *
FROM players;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :username string
-- #      :title string
INSERT OR REPLACE INTO players(uuid, username, title)
VALUES (:uuid, :username, :title);
-- #  }

-- #  { update
-- #      :uuid string
-- #      :username string
-- #      :title string
UPDATE players
SET username=:username,
    title=:title
WHERE uuid=:uuid;
-- #  }

-- #  { delete
-- #      :uuid string
DELETE FROM players
WHERE uuid=:uuid;
-- #  }
-- # }