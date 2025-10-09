I will create a new file at schema/class_change_logs.sql containing the SQL below. You can copy–paste it into PostgreSQL. It keeps logs even if a class is deleted (ON DELETE SET NULL) and adds helpful indexes.

SQL content:

```sql
-- Create table to support public.log_class_change() trigger
CREATE TABLE IF NOT EXISTS public.class_change_logs (
  log_id     BIGSERIAL PRIMARY KEY,
  class_id   INTEGER,
  operation  TEXT NOT NULL,
  changed_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  new_row    JSONB,
  old_row    JSONB,
  diff       JSONB
);

-- Keep logs when a class is deleted; update class_id on class changes
DO $$ BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint WHERE conname = 'class_change_logs_class_id_fkey'
  ) THEN
    ALTER TABLE public.class_change_logs
      ADD CONSTRAINT class_change_logs_class_id_fkey
      FOREIGN KEY (class_id) REFERENCES public.classes(class_id)
      ON UPDATE CASCADE ON DELETE SET NULL;
  END IF;
END $$;

-- Indexes for performance (idempotent)
CREATE INDEX IF NOT EXISTS idx_class_change_logs_class_id   ON public.class_change_logs (class_id);
CREATE INDEX IF NOT EXISTS idx_class_change_logs_changed_at ON public.class_change_logs (changed_at DESC);
CREATE INDEX IF NOT EXISTS idx_class_change_logs_diff_gin   ON public.class_change_logs USING GIN (diff);
```

After running this SQL, the trigger on public.classes should work without errors.