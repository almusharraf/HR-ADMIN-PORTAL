-- Mock data for testing AI scoring end-to-end. Safe to re-run (idempotent on
-- job_uuid); NOT for production use.

INSERT INTO jobs (job_uuid, title, location, department, employment_type, description, requirements, status)
VALUES
  ('a1111111-1111-4111-8111-111111111111', 'Instrumentation & Controls Engineer', 'Dammam, Saudi Arabia', 'Engineering', 'Full-time',
   'Petrogistix is hiring an Instrumentation & Controls Engineer to design, commission, and maintain control systems (PLC/DCS/SCADA) for oilfield surface equipment across Eastern Province client sites.',
   'Bachelor''s degree in Instrumentation, Electrical, or Control Systems Engineering. 3+ years experience with PLC/DCS/SCADA (Allen-Bradley, Siemens, or Honeywell). Strong understanding of P&ID and loop diagrams. Willingness to travel to field sites.',
   'open'),
  ('a2222222-2222-4222-8222-222222222222', 'HR Coordinator', 'Al Khobar, Saudi Arabia', 'Human Resources', 'Full-time',
   'We are looking for an HR Coordinator to support recruitment, onboarding, and employee records for our growing operations team.',
   'Bachelor''s degree in HR, Business Administration, or related field. 1-2 years HR experience preferred, fresh graduates with strong organizational skills are welcome to apply. Fluent in English and Arabic. Strong attention to detail.',
   'open')
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Applicants for the Instrumentation & Controls Engineer role (mixed fit)
INSERT INTO applications
  (job_id, full_name, email, phone, age, gender, nationality, current_location, city, willing_to_relocate,
   education_level, institution, specialization, status, linkedin_url, experience_json, education_json, legal_consent)
SELECT id, 'Yousef Al-Harbi', 'yousef.alharbi.mock@example.com', '+966500000001', 29, 'Male', 'Saudi Arabia',
       'Dammam', 'Dammam', 1, 'Bachelor''s Degree', 'King Fahd University of Petroleum & Minerals', 'Electrical Engineering',
       'Employed', 'https://linkedin.com/in/example-yousef',
       '[{"title":"Instrumentation Engineer","company":"Saudi Aramco (Contractor)","dates":"2020-Present"},{"title":"Junior Controls Engineer","company":"SEC","dates":"2018-2020"}]',
       '[{"degree":"BSc Electrical Engineering","institution":"KFUPM","year":"2018"}]', 1
FROM jobs WHERE job_uuid = 'a1111111-1111-4111-8111-111111111111'
UNION ALL
SELECT id, 'Fahad Al-Qahtani', 'fahad.alqahtani.mock@example.com', '+966500000002', 34, 'Male', 'Saudi Arabia',
       'Khobar', 'Khobar', 1, 'Bachelor''s Degree', 'King Saud University', 'Mechanical Engineering',
       'Employed', 'https://linkedin.com/in/example-fahad',
       '[{"title":"Field Service Engineer","company":"Schlumberger","dates":"2015-Present"}]',
       '[{"degree":"BSc Mechanical Engineering","institution":"KSU","year":"2014"}]', 1
FROM jobs WHERE job_uuid = 'a1111111-1111-4111-8111-111111111111'
UNION ALL
SELECT id, 'Layla Mansour', 'layla.mansour.mock@example.com', '+966500000003', 24, 'Female', 'Jordan',
       'Amman', 'Amman', 1, 'Bachelor''s Degree', 'University of Jordan', 'Instrumentation & Control Engineering',
       'Fresh Graduate', 'https://linkedin.com/in/example-layla',
       '[{"title":"Internship - Controls","company":"Jordan Petroleum Refinery","dates":"Summer 2023"}]',
       '[{"degree":"BSc Instrumentation & Control Engineering","institution":"University of Jordan","year":"2024"}]', 1
FROM jobs WHERE job_uuid = 'a1111111-1111-4111-8111-111111111111'
UNION ALL
SELECT id, 'Omar Siddiqui', 'omar.siddiqui.mock@example.com', '+92300000004', 41, 'Male', 'Pakistan',
       'Karachi', 'Karachi', 1, 'Diploma', 'Karachi Polytechnic', 'General Electrical Maintenance',
       'Unemployed', NULL,
       '[{"title":"Electrical Technician","company":"Local textile plant","dates":"2010-2023"}]',
       '[{"degree":"Diploma in Electrical Maintenance","institution":"Karachi Polytechnic","year":"2009"}]', 1
FROM jobs WHERE job_uuid = 'a1111111-1111-4111-8111-111111111111'
UNION ALL
SELECT id, 'Sara Al-Otaibi', 'sara.alotaibi.mock@example.com', '+966500000005', 26, 'Female', 'Saudi Arabia',
       'Riyadh', 'Riyadh', 0, 'Bachelor''s Degree', 'Princess Nourah University', 'Graphic Design',
       'Unemployed', NULL,
       '[{"title":"Freelance Graphic Designer","company":"Self-employed","dates":"2021-Present"}]',
       '[{"degree":"BA Graphic Design","institution":"Princess Nourah University","year":"2021"}]', 1
FROM jobs WHERE job_uuid = 'a1111111-1111-4111-8111-111111111111';

-- Applicants for the HR Coordinator role (mixed fit)
INSERT INTO applications
  (job_id, full_name, email, phone, age, gender, nationality, current_location, city, willing_to_relocate,
   education_level, institution, specialization, status, linkedin_url, experience_json, education_json, legal_consent)
SELECT id, 'Noura Al-Dosari', 'noura.aldosari.mock@example.com', '+966500000006', 27, 'Female', 'Saudi Arabia',
       'Khobar', 'Khobar', 1, 'Bachelor''s Degree', 'Imam Abdulrahman Bin Faisal University', 'Human Resources Management',
       'Employed', 'https://linkedin.com/in/example-noura',
       '[{"title":"HR Coordinator","company":"Local logistics firm","dates":"2021-Present"}]',
       '[{"degree":"BSc Human Resources Management","institution":"IAU","year":"2020"}]', 1
FROM jobs WHERE job_uuid = 'a2222222-2222-4222-8222-222222222222'
UNION ALL
SELECT id, 'Reem Al-Ghamdi', 'reem.alghamdi.mock@example.com', '+966500000007', 22, 'Female', 'Saudi Arabia',
       'Dammam', 'Dammam', 1, 'Bachelor''s Degree', 'King Faisal University', 'Business Administration',
       'Fresh Graduate', 'https://linkedin.com/in/example-reem',
       '[{"title":"HR Intern","company":"Petrogistix (self-reported)","dates":"Summer 2024"}]',
       '[{"degree":"BSc Business Administration","institution":"King Faisal University","year":"2024"}]', 1
FROM jobs WHERE job_uuid = 'a2222222-2222-4222-8222-222222222222'
UNION ALL
SELECT id, 'Khalid Al-Zahrani', 'khalid.alzahrani.mock@example.com', '+966500000008', 30, 'Male', 'Saudi Arabia',
       'Jubail', 'Jubail', 1, 'Bachelor''s Degree', 'Jubail University College', 'Mechanical Engineering',
       'Unemployed', NULL,
       '[{"title":"Maintenance Engineer","company":"Petrochemical plant","dates":"2017-2023"}]',
       '[{"degree":"BSc Mechanical Engineering","institution":"Jubail University College","year":"2016"}]', 1
FROM jobs WHERE job_uuid = 'a2222222-2222-4222-8222-222222222222';
