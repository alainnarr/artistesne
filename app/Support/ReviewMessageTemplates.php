<?php

namespace App\Support;

/**
 * Centralises the reviewer message templates used by the Filament "request
 * changes" actions on registration requests and change requests, avoiding
 * duplicated inline copy across the two resources.
 */
class ReviewMessageTemplates
{
    /**
     * Shared select options for the template picker.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'photo' => 'Photo non conforme',
            'biography' => 'Biographie insuffisante',
            'fields' => 'Informations manquantes',
        ];
    }

    /**
     * Templates shown when reviewing a new registration request.
     *
     * @return array<string, string>
     */
    public static function registration(): array
    {
        return [
            'photo' => "Bonjour,\n\nNous avons bien reçu votre demande de référencement. Cependant, la photo fournie ne répond pas à nos critères (résolution minimale 400×500 px, format portrait, fond neutre de préférence).\n\nMerci de soumettre une nouvelle photo conforme.\n\nCordialement,\nL'équipe Artistes.ne",
            'biography' => "Bonjour,\n\nVotre demande a bien été reçue. La biographie fournie est trop courte pour permettre une présentation satisfaisante de votre parcours.\n\nNous vous invitons à développer votre texte (idéalement 150 à 500 mots) en mettant en avant votre formation, vos projets et vos démarches artistiques.\n\nCordialement,\nL'équipe Artistes.ne",
            'fields' => "Bonjour,\n\nVotre demande a bien été reçue. Certaines informations obligatoires sont manquantes ou incomplètes (notamment : activités principales, lien de référencement cantonal).\n\nMerci de compléter votre dossier afin que nous puissions procéder à son examen.\n\nCordialement,\nL'équipe Artistes.ne",
        ];
    }

    /**
     * Templates shown when reviewing a profile change request.
     *
     * @return array<string, string>
     */
    public static function change(): array
    {
        return [
            'photo' => "Bonjour,\n\nMerci pour votre mise à jour. La photo proposée ne répond pas à nos critères (résolution minimale 400×500 px, format portrait).\n\nMerci de soumettre une nouvelle photo conforme.\n\nCordialement,\nL'équipe Artistes.ne",
            'biography' => "Bonjour,\n\nMerci pour votre mise à jour. La biographie proposée est trop courte. Nous vous invitons à la développer davantage (idéalement 150 à 500 mots).\n\nCordialement,\nL'équipe Artistes.ne",
            'fields' => "Bonjour,\n\nMerci pour votre mise à jour. Certaines informations sont incomplètes ou incorrectes. Merci de les corriger avant de resoumettre votre profil.\n\nCordialement,\nL'équipe Artistes.ne",
        ];
    }
}
