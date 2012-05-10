-- View: ""phplibObject""

-- DROP VIEW "phplib_field";

CREATE OR REPLACE VIEW "phplib_field" AS 
 SELECT pg_class.oid AS classid, pg_type.oid AS typeid, pg_class.relname, pg_attribute.attname, pg_type.typname, pg_type.typlen, pg_attribute.attnotnull, pg_attribute.atttypmod, pg_attribute.atthasdef, pg_attribute.attnum
   FROM pg_class, pg_type, pg_attribute
  WHERE pg_attribute.attrelid = pg_class.oid AND pg_attribute.atttypid = pg_type.oid AND pg_class.relnamespace = (( SELECT pg_namespace.oid
           FROM pg_namespace
          WHERE pg_namespace.nspname = current_schema())) AND pg_attribute.attnum > 0 AND NOT pg_attribute.attisdropped AND (pg_class.relkind = ANY (ARRAY['r'::"char", 'v'::"char"]));

ALTER TABLE "phplib_field" OWNER TO postgres;
