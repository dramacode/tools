PRAGMA encoding = 'UTF-8';
PRAGMA page_size = 8192;

CREATE TABLE sp (
  -- une réplique filename	act	scene	sp	who	role	verses	words	chars
  id INTEGER,        -- rowid auto
  filename     TEXT, -- nom de fichier
  act          TEXT, -- identifiant d’acte dans le fichier
  scene        TEXT, -- identifiant de scene dans le fichier
  sp           TEXT, -- identifiant de réplique dans le fichier
  role         TEXT, -- nom du personnage correspondant au code
  source       TEXT, -- code de personnage
  target       TEXT, -- code de personnage
  verses       INTEGER, -- nombre de vers
  words        INTEGER, -- nombre de mots
  chars        INTEGER, -- nombre de caractères
  text         TEXT, -- texte
  PRIMARY KEY(id ASC)
);
CREATE INDEX sp_path ON sp(filename, act, scene, sp);
CREATE INDEX sp_source ON sp(source, target);
CREATE INDEX sp_target ON sp(target, source);
