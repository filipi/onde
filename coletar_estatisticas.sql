-- coletar_estatisticas.sql
-- Idempotent statistics collection for the ONDE experience paper.
-- Target venue: Software: Practice and Experience (Wiley).
--
-- Usage:
--   psql -U <user> -d <db> -f coletar_estatisticas.sql > stats.txt
--   psql -U ideia -d ideia_onde -f coletar_estatisticas.sql > stats_grandeideia.txt
--
-- All queries are read-only and safe to run on production snapshots.
-- Output sections are delimited with =========================== banners
-- so they can be parsed by a simple post-processing script.

\pset footer off
\pset border 2

-- ===========================================================
-- 1. Deployment fingerprint
-- ===========================================================
\echo '=========================================================='
\echo '1. DEPLOYMENT FINGERPRINT'
\echo '=========================================================='
SELECT current_database()   AS database,
       current_user          AS connected_as,
       version()             AS pg_version,
       now()                 AS collected_at;

-- ===========================================================
-- 2. Global inventory of the forms table
-- ===========================================================
\echo '=========================================================='
\echo '2. FORMS INVENTORY'
\echo '=========================================================='
SELECT 'total_forms'                      AS metric, count(*)::text AS value FROM forms
UNION ALL SELECT 'with_custom_sql',
       count(*)::text
  FROM forms
 WHERE consulta IS NOT NULL AND btrim(consulta) <> ''
UNION ALL SELECT 'with_email_event',
       count(*)::text
  FROM forms
 WHERE "Enviar email para notificações" = true
UNION ALL SELECT 'with_n_to_n_caption',
       count(*)::text
  FROM forms
 WHERE "Campo(s) para utilizar como caption em relações N:N" IS NOT NULL
   AND btrim("Campo(s) para utilizar como caption em relações N:N") <> ''
UNION ALL SELECT 'with_reference_captions',
       count(*)::text
  FROM forms
 WHERE "Reference Captions" IS NOT NULL
   AND btrim("Reference Captions") <> ''
UNION ALL SELECT 'form_only_no_table',
       count(*)::text
  FROM forms
 WHERE "Apenas form, sem tabela" = true
UNION ALL SELECT 'public_no_login',
       count(*)::text
  FROM forms
 WHERE "Não exigir login para este formulário" = true
UNION ALL SELECT 'with_csv_export',
       count(*)::text
  FROM forms
 WHERE "Mostra botão para exportar para CSV" = true
UNION ALL SELECT 'distinct_tables_covered',
       count(DISTINCT tabela)::text
  FROM forms
 WHERE tabela IS NOT NULL AND btrim(tabela) <> ''
UNION ALL SELECT 'min_codigo', min(codigo)::text FROM forms
UNION ALL SELECT 'max_codigo', max(codigo)::text FROM forms
UNION ALL SELECT 'quando_min', coalesce(min(quando)::text, 'NULL') FROM forms
UNION ALL SELECT 'quando_max', coalesce(max(quando)::text, 'NULL') FROM forms;

-- ===========================================================
-- 3. Reflexivity evidence: the editor forms themselves
-- ===========================================================
\echo '=========================================================='
\echo '3. REFLEXIVITY -- editor forms'
\echo '=========================================================='
SELECT codigo, nome, titulo, tabela
  FROM forms
 WHERE tabela IN ('forms', 'menus', 'grupos', 'usuarios_grupos',
                  'forms_grupos', 'forms_permissoes',
                  'eventosdeemail', 'emailtemplates')
 ORDER BY codigo;

-- ===========================================================
-- 4. Users, groups, permissions scale
-- ===========================================================
\echo '=========================================================='
\echo '4. SCALE -- users / groups / permissions'
\echo '=========================================================='
SELECT 'usuarios',    count(*)::text FROM usuarios
UNION ALL SELECT 'grupos',      count(*)::text FROM grupos
UNION ALL SELECT 'forms_grupos', count(*)::text FROM forms_grupos
UNION ALL SELECT 'forms_permissoes', count(*)::text FROM forms_permissoes
UNION ALL SELECT 'menus_entries_to_forms',
                   count(*)::text
  FROM menus
 WHERE "Formulário" IS NOT NULL;

-- ===========================================================
-- 5. Forms per table (fan-out)
-- ===========================================================
\echo '=========================================================='
\echo '5. FORMS PER TABLE (top 30 by count)'
\echo '=========================================================='
SELECT tabela, count(*) AS num_forms
  FROM forms
 WHERE tabela IS NOT NULL AND btrim(tabela) <> ''
 GROUP BY tabela
 ORDER BY num_forms DESC, tabela
 LIMIT 30;

-- Distribution: how many tables have N forms?
\echo '----- fan-out distribution -----'
WITH per_table AS (
  SELECT tabela, count(*) AS n
    FROM forms
   WHERE tabela IS NOT NULL AND btrim(tabela) <> ''
   GROUP BY tabela
)
SELECT n AS forms_per_table, count(*) AS tables
  FROM per_table
 GROUP BY n
 ORDER BY n;

-- ===========================================================
-- 6. Column-count distribution per form
--    (proxy for complexity: length of the "campos" field)
-- ===========================================================
\echo '=========================================================='
\echo '6. FORM COMPLEXITY -- column count (approximate)'
\echo '=========================================================='
WITH cols AS (
  SELECT codigo,
         array_length(string_to_array(campos, ','), 1) AS n_cols
    FROM forms
   WHERE campos IS NOT NULL AND btrim(campos) <> ''
)
SELECT round(avg(n_cols)::numeric, 2) AS avg_cols,
       min(n_cols) AS min_cols,
       max(n_cols) AS max_cols,
       percentile_cont(0.5) WITHIN GROUP (ORDER BY n_cols) AS median_cols,
       percentile_cont(0.9) WITHIN GROUP (ORDER BY n_cols) AS p90_cols
  FROM cols;

-- ===========================================================
-- 7. Schema breadth
-- ===========================================================
\echo '=========================================================='
\echo '7. SCHEMA BREADTH'
\echo '=========================================================='
SELECT 'user_tables',
       count(*)::text
  FROM pg_class c
  JOIN pg_namespace n ON n.oid = c.relnamespace
 WHERE c.relkind = 'r'
   AND n.nspname = 'public'
UNION ALL
SELECT 'user_views',
       count(*)::text
  FROM pg_class c
  JOIN pg_namespace n ON n.oid = c.relnamespace
 WHERE c.relkind = 'v'
   AND n.nspname = 'public'
UNION ALL
SELECT 'materialized_views',
       count(*)::text
  FROM pg_matviews
 WHERE schemaname = 'public'
UNION ALL
SELECT 'foreign_keys',
       count(*)::text
  FROM information_schema.table_constraints
 WHERE table_schema = 'public'
   AND constraint_type = 'FOREIGN KEY'
UNION ALL
SELECT 'triggers',
       count(*)::text
  FROM information_schema.triggers
 WHERE trigger_schema = 'public'
UNION ALL
SELECT 'stored_procedures',
       count(*)::text
  FROM pg_proc p
  JOIN pg_namespace n ON n.oid = p.pronamespace
 WHERE n.nspname = 'public';

-- ===========================================================
-- 8. Type composition of the schema
--    (what kinds of data ONDE is actually handling)
-- ===========================================================
\echo '=========================================================='
\echo '8. TYPE COMPOSITION'
\echo '=========================================================='
SELECT t.typname, count(*) AS cols
  FROM pg_attribute a
  JOIN pg_type t       ON t.oid = a.atttypid
  JOIN pg_class c      ON c.oid = a.attrelid
  JOIN pg_namespace n  ON n.oid = c.relnamespace
 WHERE c.relkind = 'r'
   AND n.nspname = 'public'
   AND a.attnum > 0
   AND NOT a.attisdropped
 GROUP BY t.typname
 ORDER BY cols DESC
 LIMIT 20;

\echo '=========================================================='
\echo 'END OF REPORT'
\echo '=========================================================='
