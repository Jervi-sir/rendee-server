<?php

return [
    'audience' => 'partner',
    'last_updated' => '2026-10-05',
    'effective_date' => '2026-10-05',
    'terms' => [
        'title' => "Conditions Générales d'Utilisation de Rendee pour les Partenaires et Professionnels de Santé",
        'version' => '1.0',
        'summary' => 'Ce document régit les droits, obligations et abonnements professionnels des praticiens, laboratoires, pharmacies et centres de santé partenaires sur Rendee.',
        'sections' => [
            [
                'id' => 'acceptance',
                'title' => '1. Adhésion et qualification professionnelle',
                'content' => "En créant un compte professionnel sur Rendee, vous attestez de votre qualité de professionnel ou d'établissement de santé habilité et acceptez sans réserve les présentes conditions, ainsi que les règles déontologiques et légales applicables en Algérie.",
            ],
            [
                'id' => 'professional_account',
                'title' => '2. Compte professionnel et vérification des agréments',
                'content' => "Le partenaire s'engage à fournir des informations professionnelles exactes et à jour, ainsi que son numéro d'agrément ou de licence d'exercice. Rendee se réserve le droit de vérifier les autorisations administratives avant l'activation définitive du compte.",
            ],
            [
                'id' => 'appointment_management',
                'title' => '3. Gestion des rendez-vous et obligations du praticien',
                'content' => 'Le professionnel s’engage à :',
                'items' => [
                    'Traiter les demandes de rendez-vous dans des délais raisonnables.',
                    'Maintenir à jour ses créneaux de disponibilité, horaires d’ouverture et prestations offertes.',
                    'Prévenir les patients sans délai via l’application en cas de modification d’horaire ou d’annulation imprévue.',
                    'Assurer une prise en charge dénuée de toute discrimination illicite.',
                ],
            ],
            [
                'id' => 'medical_confidentiality',
                'title' => '4. Secret médical et protection des données patients',
                'content' => 'Le praticien partenaire est strictement tenu au secret professionnel et aux obligations de la loi algérienne 18-07 :',
                'items' => [
                    "N'utiliser les données des patients que pour la gestion du rendez-vous et la délivrance des soins.",
                    'Ne jamais exporter, divulguer, céder ou exploiter commercialement les données médicales.',
                    "N'accéder au dossier d'un patient qu'en présence d'une consultation ou d'un rendez-vous actif.",
                    'Garantir la sécurité physique et logique des accès et des terminaux informatiques utilisés.',
                ],
            ],
            [
                'id' => 'clinical_notes',
                'title' => '5. Cloisonnement des notes cliniques privées',
                'content' => 'Les observations cliniques consignées par le praticien demeurent strictement confidentielles au sein de son espace professionnel privé et ne sont jamais visibles par d’autres praticiens partenaires.',
            ],
            [
                'id' => 'subscriptions',
                'title' => '6. Abonnements professionnels et forfaits',
                'content' => 'Certaines fonctionnalités professionnelles (gestion d’agenda avancée, réception des rendez-vous, mise en avant) sont soumises à un abonnement :',
                'items' => [
                    'Durées proposées : 1 mois, 3 mois ou 6 mois.',
                    'Les tarifs et fonctionnalités sont communiqués préalablement à toute souscription.',
                    'Modes de règlement : virement bancaire, versement direct ou modalités convenues hors application, sans collecte de données bancaires dans l’application.',
                ],
            ],
            [
                'id' => 'manual_renewal',
                'title' => '7. Renouvellement strictement manuel et non-remboursement',
                'content' => "Les abonnements Rendee fonctionnent exclusivement par renouvellement manuel. Aucun prélèvement automatique ni tacite reconduction n'est appliqué. Le partenaire est seul responsable du renouvellement de son forfait avant échéance. Les sommes versées pour une période entamée ne sont pas remboursables, sous réserve des dispositions légales impératives.",
            ],
            [
                'id' => 'no_guarantee',
                'title' => '8. Absence de garantie de volume ou de revenus',
                'content' => "L'adhésion à Rendee n'emporte aucune garantie quant au volume de patients, au chiffre d'affaires ou à un positionnement préférentiel dans l'annuaire. Le praticien demeure seul maître de son exercice professionnel.",
            ],
            [
                'id' => 'suspension_and_termination',
                'title' => '9. Suspension et résiliation',
                'content' => "Rendee peut suspendre ou résilier l'accès d'un compte professionnel en cas de violation des conditions, défaut d'agrément en vigueur, atteinte à la confidentialité des patients ou non-renouvellement de l'abonnement.",
            ],
            [
                'id' => 'governing_law',
                'title' => '10. Droit applicable et contentieux',
                'content' => 'Les présentes conditions sont régies par le droit algérien. Tout différend relève de la compétence exclusive des tribunaux algériens compétents.',
            ],
        ],
    ],
    'privacy_policy' => [
        'title' => 'Politique de Confidentialité pour les Partenaires Professionnels',
        'version' => '1.0',
        'summary' => 'Traite de la gestion des données de votre compte professionnel, de la facturation et de vos obligations en tant que destinataire de données de santé (Loi 18-07).',
        'sections' => [
            [
                'id' => 'controller',
                'title' => '1. Responsable du traitement',
                'content' => 'Le traitement des données professionnelles est opéré par [rendeeapp] pour la plateforme Rendee. Courriel : rendee.app@gmail.com.',
            ],
            [
                'id' => 'partner_data_collected',
                'title' => '2. Données professionnelles traitées',
                'content' => 'Nous traitons :',
                'items' => [
                    'Identité professionnelle : nom, spécialité, diplômes, numéro d’agrément, photo/logo d’établissement.',
                    'Coordonnées professionnelles : adresse du cabinet/centre, géolocalisation, numéros de contact, horaires.',
                    'Prestations médicales et catalogue de services.',
                    'Historique d’abonnement, pièces justificatives de règlement et factures.',
                ],
            ],
            [
                'id' => 'public_vs_private',
                'title' => '3. Données publiques et administratives',
                'content' => 'Les informations du profil public (spécialités, horaires, localisation) sont diffusées auprès des utilisateurs pour la recherche. Les pièces administratives, agréments et pièces de paiement demeurent strictement confidentiels.',
            ],
            [
                'id' => 'provider_as_recipient',
                'title' => '4. Responsabilité du praticien sur les données reçues',
                'content' => 'Le professionnel de santé assume la responsabilité du traitement des données médicales des patients reçues dans le cadre de ses consultations, conformément aux exigences de la loi 18-07 et du code de déontologie.',
            ],
            [
                'id' => 'security_and_retention',
                'title' => '5. Conservation et comptabilité',
                'content' => 'Les écritures comptables et factures d’abonnements sont archivées pour les durées requises par la réglementation commerciale et fiscale algérienne.',
            ],
            [
                'id' => 'rights_and_contacts',
                'title' => '6. Droits du professionnel et contact',
                'content' => 'Pour toute modification de vos informations ou demande d’exercice de droits : privacy@rendee.app.',
            ],
        ],
    ],
];
