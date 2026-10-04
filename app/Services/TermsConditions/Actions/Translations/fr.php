<?php

return [
    'groups' => [
        'medical_record' => [
            'id' => 'medical_record',
            'title' => 'Dossier médical et données de santé',
            'subtitle' => 'Consentement lors de la création ou modification du dossier médical',
            'intro' => 'Vous pouvez ajouter des informations de santé facultatives à votre profil, telles que le groupe sanguin, les allergies, les affections chroniques, les traitements réguliers ou compléments, et des notes de santé.',
            'items' => [
                [
                    'id' => 'consent_health_data_processing',
                    'type' => 'toggle',
                    'required' => true,
                    'label' => "J'accepte expressément la collecte, l'enregistrement et le traitement de mes données de santé sur mon compte Rendee pour la gestion de mon dossier médical et de mes rendez-vous.",
                    'description' => 'Le traitement est strictement dédié à la tenue de votre dossier médical personnel et à vos rendez-vous conformément à la loi 18-07.',
                    'default_value' => false,
                ],
                [
                    'id' => 'consent_health_data_sharing_with_provider',
                    'type' => 'toggle',
                    'required' => true,
                    'label' => "J'accepte de mettre les données de mon dossier médical à disposition du médecin ou professionnel de santé auprès duquel je prends rendez-vous via Rendee, afin de faciliter la prise en charge et le suivi du rendez-vous.",
                    'description' => "Les données ne sont accessibles qu'au praticien réservé pendant la période de prise en charge.",
                    'default_value' => false,
                ],
            ],
            'notice' => 'Vous pouvez retirer ce consentement à tout moment depuis vos paramètres, sachant que cela peut restreindre certaines fonctionnalités du dossier médical ou de la réservation.',
        ],

        'patient_registration' => [
            'id' => 'patient_registration',
            'title' => 'Conditions et consentements patient',
            'subtitle' => 'Consentement de l’utilisateur lors de l’inscription',
            'intro' => 'Veuillez prendre connaissance des conditions et autorisations suivantes avant de finaliser votre inscription sur Rendee.',
            'items' => [
                [
                    'id' => 'agree_patient_terms_privacy',
                    'type' => 'checkbox',
                    'required' => true,
                    'label' => "J'accepte les conditions d'utilisation et la politique de confidentialité de l'application Rendee.",
                    'description' => "Ces conditions précisent les limites de responsabilité et le statut d'intermédiaire de Rendee (hors urgences).",
                    'default_value' => false,
                ],
                [
                    'id' => 'consent_appointment_notifications',
                    'type' => 'toggle',
                    'required' => false,
                    'label' => "J'accepte de recevoir des notifications et alertes relatives à mes rendez-vous et à mon compte.",
                    'description' => 'Permet de recevoir les confirmations, rappels et modifications de statut de vos consultations.',
                    'default_value' => true,
                ],
            ],
            'notice' => 'Vous pouvez consulter l’intégralité des conditions et de la politique de confidentialité à tout moment dans les réglages.',
        ],

        'partner_onboarding' => [
            'id' => 'partner_onboarding',
            'title' => 'Conditions d’adhésion des partenaires de santé',
            'subtitle' => 'Engagements et consentements pour le compte professionnel',
            'intro' => 'Pour finaliser l’activation de votre compte professionnel Rendee, veuillez confirmer les engagements professionnels ci-dessous :',
            'items' => [
                [
                    'id' => 'agree_partner_terms',
                    'type' => 'checkbox',
                    'required' => true,
                    'label' => "J'accepte les conditions d'utilisation du compte professionnel et la politique de confidentialité de Rendee.",
                    'description' => 'Engagement à respecter les règles de déontologie et la réglementation en vigueur.',
                    'default_value' => false,
                ],
                [
                    'id' => 'pledge_patient_confidentiality',
                    'type' => 'toggle',
                    'required' => true,
                    'label' => "Je m'engage au respect du secret médical et à n'utiliser les données des patients qu'aux strictes fins de prise en charge et de soins, sans aucune diffusion ou exploitation commerciale.",
                    'description' => 'Le respect de la confidentialité des données de santé est une obligation légale et déontologique (Loi 18-07).',
                    'default_value' => false,
                ],
                [
                    'id' => 'acknowledge_manual_renewal',
                    'type' => 'checkbox',
                    'required' => true,
                    'label' => "Je reconnais que l'abonnement professionnel fonctionne uniquement par renouvellement manuel, sans prélèvement ni reconduction tacite automatique.",
                    'description' => 'Le renouvellement relève de l’initiative du partenaire avant la date d’échéance pour maintenir la visibilité.',
                    'default_value' => false,
                ],
            ],
            'notice' => 'Le compte professionnel fait l’objet d’une vérification des accréditations professionnelles avant son activation finale.',
        ],
    ],
];
