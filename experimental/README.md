# Fonctionnalités Expérimentales

Ce dossier contient du code mis en veille : des fonctionnalités prometteuses mais hors du scope du Starter Thème Core.

## `ai-bridge.php` + `admin-ai.jsx`

**Contexte :** Un pont d'API REST WordPress (PHP) et un plugin Gutenberg (React) permettant à l'utilisateur final (le client de l'agence) de générer du contenu de blocs ACF directement depuis l'éditeur WordPress, en utilisant sa propre clé OpenAI (Approche "Zero Trust" via `localStorage`). Intégrait également la génération et le sideload d'images DALL-E 3.

**Pourquoi mis en veille ?**
L'objectif de `vibrisse-core` est d'être un outil **pour l'agence (côté dev)**. Intégrer l'IA côté client final (Admin WordPress) dilue la proposition de valeur et introduit une complexité de sécurité, de maintenance et d'UX hors-scope.

**Future piste :** Ce code est la base d'un plugin autonome `vibrisse-ai-content` (potentiellement premium) qui pourrait se greffer sur le thème.
