<?php

return [
    'audience' => 'patient',
    'last_updated' => '2026-10-05',
    'effective_date' => '2026-10-05',
    'terms' => [
        'title' => "Conditions Générales d'Utilisation de Rendee pour les Patients et Utilisateurs",
        'version' => '1.0',
        'summary' => "Ce document régit l'accès et l'utilisation de la plateforme Rendee par les patients pour la prise de rendez-vous et la gestion du dossier médical.",
        'sections' => [
            [
                'id' => 'acceptance',
                'title' => '1. Acceptation des conditions',
                'content' => "En accédant à l'application Rendee ou en créant un compte, vous reconnaissez avoir lu, compris et accepté sans réserve les présentes conditions d'utilisation. Si vous refusez ces conditions, veuillez ne pas utiliser l'application.",
            ],
            [
                'id' => 'service_nature',
                'title' => '2. Nature du service Rendee',
                'content' => "Rendee est une plateforme numérique intermédiaire facilitant la recherche de professionnels de santé et l'organisation des rendez-vous. Rendee n'est pas un établissement de santé, ne fournit aucun diagnostic, traitement, consultation ou ordonnance médicale, et ne remplace en aucun cas l'avis direct d'un praticien.",
            ],
            [
                'id' => 'emergency_disclaimer',
                'title' => '3. Situations d’urgence médicale',
                'content' => "Rendee ne doit en aucun cas être utilisé dans les situations d'urgence ou de détresse vitale. En cas d'urgence, veuillez contacter immédiatement les services d'urgence officiels ou vous rendre à la structure hospitalière la plus proche.",
            ],
            [
                'id' => 'user_account',
                'title' => '4. Compte personnel et obligations du patient',
                'content' => "Lors de la création et de l'utilisation de votre compte, vous vous engagez à :",
                'items' => [
                    'Fournir des données exactes, complètes et à jour (nom, prénom, numéro de téléphone, date de naissance).',
                    'Préserver la confidentialité stricte de vos identifiants de connexion et de votre mot de passe.',
                    'Demeurer responsable de toute activité réalisée depuis votre compte.',
                    "Avertir immédiatement Rendee en cas de soupçon d'accès non autorisé ou de faille de sécurité.",
                ],
            ],
            [
                'id' => 'appointments',
                'title' => '5. Prise de rendez-vous et réservations',
                'content' => 'Lors de la soumission d’une demande de rendez-vous :',
                'items' => [
                    "La demande est transmise au praticien choisi et n'est définitive qu'une fois confirmée dans l'application.",
                    'Le professionnel de santé se réserve le droit de confirmer, proposer un autre horaire ou annuler pour motif légitime.',
                    "Le patient s'engage à se présenter à l'heure convenue ou à annuler via l'application au préalable.",
                    "Tout abus ou réservation fictive répétée peut entraîner la suspension ou clôture immédiate du compte.",
                ],
            ],
            [
                'id' => 'medical_record',
                'title' => '6. Dossier médical et données de santé sensibles',
                'content' => "L'utilisateur peut constituer un dossier médical personnel facultatif (groupe sanguin, allergies, pathologies chroniques, traitements en cours). L'utilisateur est responsable de la véracité de ces informations et consent à leur communication au praticien réservé aux fins de faciliter la prise en charge médicale.",
            ],
            [
                'id' => 'fees_and_payments',
                'title' => '7. Tarification et absence de paiement en ligne',
                'content' => "L'inscription et la prise de rendez-vous sur Rendee sont gratuites pour les patients. L'application n'intègre actuellement aucun module de paiement en ligne pour les patients. Les honoraires de consultation sont réglés directement auprès du professionnel de santé.",
            ],
            [
                'id' => 'prohibited_uses',
                'title' => '8. Comportements prohibés',
                'content' => "Il est strictement interdit d'usurper l'identité d'un tiers, de tenter de compromettre la sécurité de l'infrastructure, d'extraire frauduleusement des données ou d'utiliser la plateforme à des fins illégales.",
            ],
            [
                'id' => 'liability',
                'title' => '9. Limitation de responsabilité',
                'content' => "Rendee décline toute responsabilité quant aux actes médicaux, diagnostics, prescriptions et décisions cliniques qui relèvent de la responsabilité exclusive du professionnel de santé habilité.",
            ],
            [
                'id' => 'governing_law',
                'title' => '10. Droit applicable et juridiction compétente',
                'content' => 'Les présentes conditions sont régies et interprétées selon le droit de la République Algérienne Démocratique et Populaire. Tout litige relève de la compétence exclusive des juridictions algériennes.',
            ],
            [
                'id' => 'contact',
                'title' => '11. Contact et assistance',
                'content' => 'Pour toute question relative aux présentes conditions, contactez : rendee.app@gmail.com ou par téléphone au 0799442496.',
            ],
        ],
    ],
    'privacy_policy' => [
        'title' => 'Politique de Confidentialité pour les Patients et Utilisateurs',
        'version' => '1.0',
        'summary' => 'Présentation de la collecte, de la protection et du traitement de vos données personnelles et de santé dans le respect de la loi algérienne 18-07.',
        'sections' => [
            [
                'id' => 'controller',
                'title' => '1. Responsable du traitement',
                'content' => 'Le responsable du traitement des données est [rendeeapp] exploitant la plateforme Rendee. Courriel : rendee.app@gmail.com - Tél : 0799662496.',
            ],
            [
                'id' => 'data_collected',
                'title' => '2. Données personnelles et médicales collectées',
                'content' => 'Nous collectons les données indispensables au bon fonctionnement du service :',
                'items' => [
                    'Compte utilisateur : nom, prénom, numéro de téléphone, e-mail, date de naissance, genre, wilaya/commune.',
                    'Rendez-vous : nom du praticien/centre, spécialité demandée, date, créneau horaire, notes associées.',
                    'Données de santé sensibles : groupe sanguin, allergies, antécédents, traitements, saisies facultatives avec consentement explicite.',
                    'Contact d’urgence : coordonnées de la personne à prévenir en cas de nécessité (optionnel).',
                    'Données techniques : identifiants de l’appareil, adresse IP, journaux de navigation et de sécurité.',
                ],
            ],
            [
                'id' => 'purpose',
                'title' => '3. Finalités du traitement des données',
                'content' => 'Vos données sont collectées pour :',
                'items' => [
                    'Créer, authentifier et sécuriser votre compte utilisateur.',
                    'Transmettre et gérer vos demandes de rendez-vous auprès des praticiens sélectionnés.',
                    'Permettre la tenue et le partage sécurisé de votre dossier médical avec le médecin traitant.',
                    'Vous faire parvenir les notifications de confirmation, rappels et alertes de suivi.',
                    'Respecter nos obligations légales en Algérie et prévenir les fraudes.',
                ],
            ],
            [
                'id' => 'legal_basis',
                'title' => '4. Base légale et consentement',
                'content' => 'Les traitements sont fondés sur la loi n° 18-07 relative à la protection des personnes physiques dans le traitement des données à caractère personnel et sur votre consentement libre, préalable et éclairé.',
            ],
            [
                'id' => 'data_sharing',
                'title' => '5. Partage et non-cession des données',
                'content' => 'Les données de santé ne sont partagées qu’avec le praticien auprès duquel vous avez réservé. Rendee ne vend, ne loue et ne monétise jamais vos données de santé, et ne réalise aucun profilage publicitaire fondé sur votre état de santé.',
            ],
            [
                'id' => 'doctor_notes_isolation',
                'title' => '6. Confidentialité et cloisonnement des notes du médecin',
                'content' => 'Les notes cliniques internes rédigées par un praticien sont strictement cantonnées à son espace professionnel sécurisé et ne sont jamais transmises à un autre praticien sans votre accord.',
            ],
            [
                'id' => 'security_and_retention',
                'title' => '7. Sécurité et durées de conservation',
                'content' => 'Vos données sont protégées par chiffrement, contrôle strict des accès et hébergement sécurisé. Elles sont conservées tant que le compte est actif et conformément aux durées légales d’archivage.',
            ],
            [
                'id' => 'user_rights',
                'title' => '8. Vos droits légaux (Loi 18-07)',
                'content' => 'Vous disposez d’un droit d’accès, de rectification, d’actualisation, de suppression et d’opposition légitime, ainsi que du retrait de consentement via : privacy@rendee.app.',
            ],
            [
                'id' => 'minors',
                'title' => '9. Mineurs',
                'content' => 'L’utilisation du service et le renseignement de données médicales pour un mineur nécessitent l’accord préalable et la supervision de son représentant légal.',
            ],
        ],
    ],
];
