-- WeCoza Clients Plugin Database Schema
-- Table: clients
-- Database: PostgreSQL
-- Version: 1.0.0

-- Drop table if exists (for development only)
-- DROP TABLE IF EXISTS clients CASCADE;

-- Create clients table
CREATE TABLE IF NOT EXISTS clients (
    id SERIAL PRIMARY KEY,
    
    -- Basic Information
    client_name VARCHAR(255) NOT NULL,
    branch_of INTEGER REFERENCES clients(id) ON DELETE SET NULL,
    company_registration_nr VARCHAR(100) NOT NULL UNIQUE,
    
    -- Address Information
    client_street_address VARCHAR(500) NOT NULL,
    client_suburb VARCHAR(255) NOT NULL,
    client_town VARCHAR(255) NOT NULL,
    client_postal_code VARCHAR(20) NOT NULL,
    
    -- Contact Information
    contact_person VARCHAR(255) NOT NULL,
    contact_person_email VARCHAR(255) NOT NULL,
    contact_person_cellphone VARCHAR(50) NOT NULL,
    contact_person_tel VARCHAR(50),
    
    -- Business Information
    client_communication VARCHAR(50) NOT NULL CHECK (client_communication IN ('Cold Call', 'Lead', 'Active Client', 'Lost Client')),
    seta VARCHAR(50) NOT NULL,
    client_status VARCHAR(50) NOT NULL CHECK (client_status IN ('Cold Call', 'Lead', 'Active Client', 'Lost Client')),
    financial_year_end DATE NOT NULL,
    bbbee_verification_date DATE NOT NULL,
    
    -- File Uploads
    quotes TEXT, -- Path to uploaded quote files
    
    -- Class Related Fields (JSONB for flexibility)
    current_classes JSONB DEFAULT '[]'::jsonb,
    stopped_classes JSONB DEFAULT '[]'::jsonb,
    deliveries JSONB DEFAULT '[]'::jsonb,
    collections JSONB DEFAULT '[]'::jsonb,
    cancellations JSONB DEFAULT '[]'::jsonb,
    
    -- Date Fields
    class_restarts DATE,
    class_stops DATE,
    
    -- Assessment Fields (JSONB for flexibility)
    assessments JSONB DEFAULT '[]'::jsonb,
    progressions JSONB DEFAULT '[]'::jsonb,
    
    -- Metadata
    created_by INTEGER,
    updated_by INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP -- Soft delete support
);

-- Create indexes for better performance
CREATE INDEX idx_clients_name ON clients(client_name);
CREATE INDEX idx_clients_status ON clients(client_status);
CREATE INDEX idx_clients_communication ON clients(client_communication);
CREATE INDEX idx_clients_seta ON clients(seta);
CREATE INDEX idx_clients_town ON clients(client_town);
CREATE INDEX idx_clients_deleted_at ON clients(deleted_at);
CREATE INDEX idx_clients_branch_of ON clients(branch_of);
CREATE INDEX idx_clients_company_reg ON clients(company_registration_nr);

-- Create GIN indexes for JSONB columns
CREATE INDEX idx_clients_current_classes ON clients USING GIN (current_classes);
CREATE INDEX idx_clients_assessments ON clients USING GIN (assessments);

-- Create trigger to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_clients_updated_at BEFORE UPDATE
    ON clients FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Create client_meta table for additional flexible data
CREATE TABLE IF NOT EXISTS client_meta (
    id SERIAL PRIMARY KEY,
    client_id INTEGER NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
    meta_key VARCHAR(255) NOT NULL,
    meta_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(client_id, meta_key)
);

-- Create index for client_meta
CREATE INDEX idx_client_meta_client_id ON client_meta(client_id);
CREATE INDEX idx_client_meta_key ON client_meta(meta_key);

-- Create client_notes table for tracking client interactions
CREATE TABLE IF NOT EXISTS client_notes (
    id SERIAL PRIMARY KEY,
    client_id INTEGER NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
    note TEXT NOT NULL,
    note_type VARCHAR(50) DEFAULT 'general',
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for client_notes
CREATE INDEX idx_client_notes_client_id ON client_notes(client_id);
CREATE INDEX idx_client_notes_type ON client_notes(note_type);
CREATE INDEX idx_client_notes_created_at ON client_notes(created_at);

-- Create view for active clients
CREATE OR REPLACE VIEW active_clients AS
SELECT * FROM clients 
WHERE client_status = 'Active Client' 
AND deleted_at IS NULL;

-- Create view for client statistics
CREATE OR REPLACE VIEW client_statistics AS
SELECT 
    COUNT(*) as total_clients,
    COUNT(CASE WHEN client_status = 'Active Client' THEN 1 END) as active_clients,
    COUNT(CASE WHEN client_status = 'Lead' THEN 1 END) as leads,
    COUNT(CASE WHEN client_status = 'Cold Call' THEN 1 END) as cold_calls,
    COUNT(CASE WHEN client_status = 'Lost Client' THEN 1 END) as lost_clients,
    COUNT(CASE WHEN branch_of IS NOT NULL THEN 1 END) as branch_clients
FROM clients
WHERE deleted_at IS NULL;

-- Sample SETA values for reference
-- AgriSETA, BANKSETA, CATHSSETA, CETA, CHIETA, ETDP SETA, EWSETA, FASSET, 
-- FP&M SETA, FoodBev SETA, HWSETA, INSETA, LGSETA, MICT SETA, MQA, PSETA, 
-- SASSETA, Services SETA, TETA, W&RSETA, merSETA