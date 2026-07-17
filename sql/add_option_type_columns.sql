ALTER TABLE polls ADD COLUMN option_type VARCHAR(20) DEFAULT 'text' AFTER is_anonymous;
ALTER TABLE poll_options ADD COLUMN option_image VARCHAR(500) DEFAULT NULL AFTER option_text;
