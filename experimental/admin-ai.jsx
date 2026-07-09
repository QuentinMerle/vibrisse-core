/**
 * Vibrisse Core - AI Bridge pour Gutenberg
 * Gère l'UI (Zero Trust LocalStorage) et la communication avec l'API REST.
 */

const { registerPlugin } = wp.plugins;
const { PluginBlockSettingsMenuItem } = wp.editPost;
const { useState, useEffect } = wp.element;
const { Modal, Button, TextControl, PanelBody, Notice, Spinner } = wp.components;
const { useSelect, useDispatch } = wp.data;

const AIGeneratorModal = ({ isOpen, onClose, blockId, blockName }) => {
    const [apiKey, setApiKey] = useState(localStorage.getItem('vibrisse_openai_key') || '');
    const [isGenerating, setIsGenerating] = useState(false);
    const [error, setError] = useState('');
    const { updateBlockAttributes } = useDispatch('core/block-editor');
    
    // Obtenir le contenu actuel du bloc (les champs ACF existants)
    const blockAttributes = useSelect((select) => {
        const block = select('core/block-editor').getBlock(blockId);
        return block ? block.attributes : null;
    }, [blockId]);

    if (!isOpen) return null;

    const handleGenerate = async () => {
        if (!apiKey) {
            setError("Veuillez saisir votre clé API OpenAI.");
            return;
        }

        // Sauvegarde de la clé dans le localStorage (Zero Trust)
        localStorage.setItem('vibrisse_openai_key', apiKey);
        setIsGenerating(true);
        setError('');

        try {
            // Appel au pont PHP via l'API REST
            const response = await fetch('/wp-json/vibrisse/v1/generate-block', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Vibrisse-API-Key': apiKey, // Clé éphémère dans le Header
                    'X-WP-Nonce': wpApiSettings.nonce // Sécurité WordPress
                },
                body: JSON.stringify({
                    block_type: blockName,
                    current_data: blockAttributes?.data || {}
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || "Erreur lors de la génération par l'IA.");
            }

            // Mise à jour magique des attributs ACF du bloc ciblé
            updateBlockAttributes(blockId, {
                data: {
                    ...blockAttributes.data,
                    ...result.data // Les nouvelles valeurs générées par l'IA (et potentiellement les IDs d'images générées)
                }
            });

            onClose(); // Ferme le modal après succès

        } catch (err) {
            setError(err.message);
        } finally {
            setIsGenerating(false);
        }
    };

    return (
        <Modal title="✨ Magie IA (Contenu)" onRequestClose={onClose} style={{maxWidth: '500px'}}>
            <PanelBody>
                <p style={{marginBottom: '15px', color: '#666', fontSize: '14px'}}>
                    L'IA va lire la charte métier (CLIENT.md) et générer un contenu sur-mesure pour ce bloc <strong>{blockName}</strong>.
                </p>
                
                <TextControl
                    label="Clé API OpenAI (Stockée localement)"
                    type="password"
                    value={apiKey}
                    onChange={(val) => setApiKey(val)}
                    help="Votre clé ne quitte jamais votre navigateur et n'est pas stockée en base de données. Elle est utilisée de manière éphémère (Zero Trust)."
                />

                {error && <Notice status="error" isDismissible={false}>{error}</Notice>}

                <div style={{ marginTop: '20px', display: 'flex', justifyContent: 'flex-end', gap: '10px' }}>
                    <Button isSecondary onClick={onClose} disabled={isGenerating}>Annuler</Button>
                    <Button isPrimary onClick={handleGenerate} disabled={isGenerating} style={{display: 'flex', gap: '8px', alignItems: 'center'}}>
                        {isGenerating ? <><Spinner /> <span>Création en cours...</span></> : 'Générer le contenu'}
                    </Button>
                </div>
            </PanelBody>
        </Modal>
    );
};

// Injection du bouton dans le menu des paramètres du bloc (les 3 petits points dans la toolbar)
const AIGeneratorPlugin = () => {
    const [isModalOpen, setIsModalOpen] = useState(false);

    // On récupère le bloc actuellement sélectionné
    const selectedBlock = useSelect((select) => select('core/block-editor').getSelectedBlock(), []);

    // Si pas de bloc sélectionné ou si ce n'est pas un bloc ACF de notre thème, on ne fait rien.
    // L'IA est réservée aux blocs ACF custom (qui commencent par 'vibrisse/')
    if (!selectedBlock || !selectedBlock.name.startsWith('vibrisse/')) {
        return null;
    }

    return (
        <>
            <PluginBlockSettingsMenuItem
                icon="admin-customizer"
                label="✨ Générer (IA)"
                onClick={() => setIsModalOpen(true)}
            />
            <AIGeneratorModal 
                isOpen={isModalOpen} 
                onClose={() => setIsModalOpen(false)} 
                blockId={selectedBlock.clientId} 
                blockName={selectedBlock.name}
            />
        </>
    );
};

// Enregistrement du plugin Gutenberg
registerPlugin('vibrisse-ai-bridge', {
    render: AIGeneratorPlugin,
});
