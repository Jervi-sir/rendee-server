<?php

return [
    'audience' => 'partner',
    'last_updated' => '2026-10-05',
    'effective_date' => '2026-10-05',
    'terms' => [
        'title' => 'Rendee Terms of Service for Healthcare Partners and Professionals',
        'version' => '1.0',
        'summary' => 'Defines the professional rights, obligations, and subscription policies governing healthcare providers (physicians, laboratories, pharmacies, centers) on Rendee.',
        'sections' => [
            [
                'id' => 'acceptance',
                'title' => '1. Acceptance and Professional Qualification',
                'content' => 'By creating a professional account on Rendee, you affirm your status as a duly licensed healthcare professional or accredited healthcare institution, and agree to strictly comply with these terms, professional ethics, and applicable Algerian laws.',
            ],
            [
                'id' => 'professional_account',
                'title' => '2. Professional Account and License Verification',
                'content' => 'The partner agrees to provide true, accurate, and current professional information, including a valid professional accreditation or license number. Accounts undergo administrative review by Rendee prior to final activation.',
            ],
            [
                'id' => 'appointment_management',
                'title' => '3. Appointment Management and Practitioner Obligations',
                'content' => 'The provider agrees to:',
                'items' => [
                    'Process appointment requests promptly and within reasonable timeframes.',
                    'Keep operating hours, availability schedules, and service catalogs up to date.',
                    'Promptly notify patients through the app if rescheduling or cancellation is unavoidable.',
                    'Provide care without unlawful discrimination.',
                ],
            ],
            [
                'id' => 'medical_confidentiality',
                'title' => '4. Medical Secrecy and Patient Data Protection',
                'content' => 'The provider is bound by statutory medical confidentiality and Algerian Law 18-07:',
                'items' => [
                    'Use patient personal and health data solely for appointment management and clinical care.',
                    'Never export, share, sell, or commercially exploit patient health data.',
                    'Access patient records only in connection with an active, authorized appointment.',
                    'Safeguard credentials and maintain adequate physical and digital security on consulting devices.',
                ],
            ],
            [
                'id' => 'clinical_notes',
                'title' => '5. Isolation of Private Clinical Notes',
                'content' => 'Clinical notes and internal remarks documented by the provider remain confined to their private workspace and are never disclosed to other healthcare practitioners.',
            ],
            [
                'id' => 'subscriptions',
                'title' => '6. Professional Subscriptions and Plans',
                'content' => 'Advanced professional features (intake of appointments, profile listing, calendar management) require a paid subscription:',
                'items' => [
                    'Available terms: 1 month, 3 months, or 6 months.',
                    'Pricing and plan features are specified prior to activation or renewal.',
                    'Payment methods: agreed external channels (wire transfer, direct payment) with no in-app credit card data collection.',
                ],
            ],
            [
                'id' => 'manual_renewal',
                'title' => '7. Manual Renewal Only and Non-Refundable Policy',
                'content' => 'Subscriptions operate exclusively on manual renewal. There are no recurring auto-debits or automatic extensions. The provider is solely responsible for renewing before the expiration date to maintain listing visibility and booking intake. Payments made for started subscription terms are non-refundable, subject to mandatory legal rights.',
            ],
            [
                'id' => 'no_guarantee',
                'title' => '8. No Guarantee of Patient Volume or Revenue',
                'content' => 'Subscription to Rendee does not guarantee a minimum number of patient bookings, revenue levels, or exclusive ranking in search results. The provider remains solely responsible for professional services and clinical practice.',
            ],
            [
                'id' => 'suspension_and_termination',
                'title' => '9. Suspension and Termination',
                'content' => 'Rendee reserves the right to suspend or terminate accounts in the event of breach of terms, loss of valid accreditation, violation of patient confidentiality, or subscription lapse.',
            ],
            [
                'id' => 'governing_law',
                'title' => '10. Governing Law and Disputes',
                'content' => 'These terms are governed by the laws of Algeria. Any disputes shall be submitted exclusively to the competent Algerian courts.',
            ],
        ],
    ],
    'privacy_policy' => [
        'title' => 'Privacy Policy for Healthcare Partners',
        'version' => '1.0',
        'summary' => 'Outlines the handling of professional account information, subscription records, and your role as a data recipient under Law 18-07.',
        'sections' => [
            [
                'id' => 'controller',
                'title' => '1. Data Controller',
                'content' => 'Professional data is managed by [rendeeapp] for the Rendee platform. Email: rendee.app@gmail.com.',
            ],
            [
                'id' => 'partner_data_collected',
                'title' => '2. Professional Information Collected',
                'content' => 'We process:',
                'items' => [
                    'Professional identity: full name, specialty, qualifications, license/accreditation number, profile photo or institution logo.',
                    'Practice location: clinic address, wilaya, commune, GPS coordinates, business contact details, hours.',
                    'Catalog of medical services and indicative fees.',
                    'Subscription records: plan type, valid dates, payment confirmations, and invoices.',
                ],
            ],
            [
                'id' => 'public_vs_private',
                'title' => '3. Public Information vs Confidential Records',
                'content' => 'Public profile details (specialties, hours, address) are displayed to users searching for care. Verification licenses, receipts, and internal accounting files remain strictly confidential.',
            ],
            [
                'id' => 'provider_as_recipient',
                'title' => '4. Partner Obligations as Data Recipient',
                'content' => 'The provider acts as an independent data controller/recipient for patient health records received for clinical appointments and must uphold appropriate organizational and technical safeguards pursuant to Law 18-07.',
            ],
            [
                'id' => 'security_and_retention',
                'title' => '5. Record Keeping and Retention',
                'content' => 'Accounting and subscription records are preserved for the statutory retention periods required by Algerian commercial and fiscal regulations.',
            ],
            [
                'id' => 'rights_and_contacts',
                'title' => '6. Partner Rights and Inquiries',
                'content' => 'To update professional credentials or make inquiries regarding data practices, contact: privacy@rendee.app.',
            ],
        ],
    ],
];
