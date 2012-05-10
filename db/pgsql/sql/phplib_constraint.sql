-- View: ""phplibConstraint""

DROP VIEW "phplibConstraint";

CREATE OR REPLACE VIEW "phplib_constraint" AS 
 SELECT pg_constraint.conname, pg_constraint.contype, pg_constraint.conrelid AS local_object, pg_constraint.conkey AS local_keys, pg_constraint.confrelid AS foreign_object, pg_constraint.confkey AS foreign_keys, pg_constraint.confupdtype AS on_update, pg_constraint.confdeltype AS on_delete
   FROM pg_constraint
  WHERE pg_constraint.connamespace = (( SELECT pg_namespace.oid
           FROM pg_namespace
          WHERE pg_namespace.nspname = current_schema()));

ALTER TABLE "phplib_constraint" OWNER TO postgres;
