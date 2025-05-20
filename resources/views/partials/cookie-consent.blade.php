<div id="cookie-consent-banner" class="cookie-banner" style="display: none;">
    <div class="cookie-content">
        <div class="cookie-text">
            <h3>Sua privacidade é importante</h3>
            <p>Utilizamos cookies para melhorar sua experiência, fornecer conteúdo personalizado, análises e marketing. 
            Ao clicar em "Aceitar", você concorda com nosso uso de cookies para esses fins.</p>
            <p>Você pode gerenciar suas preferências a qualquer momento.</p>
        </div>
        <div class="cookie-actions">
            <button id="cookie-accept-all" class="cookie-btn cookie-btn-primary">Aceitar todos</button>
            <button id="cookie-accept-essential" class="cookie-btn">Apenas essenciais</button>
            <button id="cookie-settings" class="cookie-btn cookie-btn-text">Configurações</button>
        </div>
    </div>
</div>

<div id="cookie-settings-modal" class="cookie-modal" style="display: none;">
    <div class="cookie-modal-content">
        <div class="cookie-modal-header">
            <h3>Configurações de cookies</h3>
            <button id="cookie-modal-close" class="cookie-modal-close">&times;</button>
        </div>
        <div class="cookie-modal-body">
            <div class="cookie-preference">
                <label class="cookie-switch">
                    <input type="checkbox" id="essential-cookies" checked disabled>
                    <span class="cookie-slider"></span>
                </label>
                <div class="cookie-preference-text">
                    <h4>Cookies essenciais</h4>
                    <p>Necessários para o funcionamento básico do site. Não podem ser desativados.</p>
                </div>
            </div>
            <div class="cookie-preference">
                <label class="cookie-switch">
                    <input type="checkbox" id="analytics-cookies">
                    <span class="cookie-slider"></span>
                </label>
                <div class="cookie-preference-text">
                    <h4>Cookies analíticos</h4>
                    <p>Nos ajudam a entender como os visitantes interagem com o site, permitindo melhorias.</p>
                </div>
            </div>
            <div class="cookie-preference">
                <label class="cookie-switch">
                    <input type="checkbox" id="marketing-cookies">
                    <span class="cookie-slider"></span>
                </label>
                <div class="cookie-preference-text">
                    <h4>Cookies de marketing</h4>
                    <p>Utilizados para rastrear os visitantes entre sites para exibir anúncios relevantes.</p>
                </div>
            </div>
            <div class="cookie-preference">
                <label class="cookie-switch">
                    <input type="checkbox" id="preference-cookies">
                    <span class="cookie-slider"></span>
                </label>
                <div class="cookie-preference-text">
                    <h4>Cookies de preferências</h4>
                    <p>Permitem que o site lembre configurações que mudam a aparência ou comportamento do site.</p>
                </div>
            </div>
        </div>
        <div class="cookie-modal-footer">
            <button id="cookie-save-preferences" class="cookie-btn cookie-btn-primary">Salvar preferências</button>
        </div>
    </div>
</div>

<style>
    .cookie-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: #ffffff;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        padding: 16px;
        z-index: 9999;
        border-top: 1px solid #e5e7eb;
    }
    
    .cookie-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
    }
    
    @media (min-width: 768px) {
        .cookie-content {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
    
    .cookie-text {
        flex: 2;
    }
    
    .cookie-text h3 {
        font-size: 18px;
        margin: 0 0 8px 0;
        color: #1f2937;
    }
    
    .cookie-text p {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #4b5563;
    }
    
    .cookie-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 16px;
        flex: 1;
        justify-content: flex-end;
    }
    
    @media (min-width: 768px) {
        .cookie-actions {
            margin-top: 0;
            margin-left: 24px;
        }
    }
    
    .cookie-btn {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        background-color: #f9fafb;
        color: #4b5563;
        transition: all 0.2s;
    }
    
    .cookie-btn:hover {
        background-color: #f3f4f6;
    }
    
    .cookie-btn-primary {
        background-color: #3563e9;
        color: white;
        border-color: #3563e9;
    }
    
    .cookie-btn-primary:hover {
        background-color: #2a4fba;
    }
    
    .cookie-btn-text {
        background-color: transparent;
        border-color: transparent;
    }
    
    .cookie-btn-text:hover {
        background-color: #f9fafb;
    }
    
    /* Modal Styles */
    .cookie-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    
    .cookie-modal-content {
        background-color: white;
        border-radius: 8px;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .cookie-modal-header {
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .cookie-modal-header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .cookie-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #9ca3af;
    }
    
    .cookie-modal-body {
        padding: 16px;
    }
    
    .cookie-modal-footer {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        text-align: right;
    }
    
    .cookie-preference {
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
    }
    
    .cookie-preference-text {
        margin-left: 16px;
    }
    
    .cookie-preference-text h4 {
        margin: 0 0 4px 0;
        font-size: 16px;
    }
    
    .cookie-preference-text p {
        margin: 0;
        font-size: 14px;
        color: #6b7280;
    }
    
    /* Switch Styles */
    .cookie-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        margin-top: 3px;
    }
    
    .cookie-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .cookie-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e7eb;
        transition: .4s;
        border-radius: 34px;
    }
    
    .cookie-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .cookie-slider {
        background-color: #3563e9;
    }
    
    input:disabled + .cookie-slider {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    input:checked + .cookie-slider:before {
        transform: translateX(26px);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cookieBanner = document.getElementById('cookie-consent-banner');
        const cookieModal = document.getElementById('cookie-settings-modal');
        const acceptAllBtn = document.getElementById('cookie-accept-all');
        const acceptEssentialBtn = document.getElementById('cookie-accept-essential');
        const settingsBtn = document.getElementById('cookie-settings');
        const modalCloseBtn = document.getElementById('cookie-modal-close');
        const savePreferencesBtn = document.getElementById('cookie-save-preferences');
        
        const analyticsCookies = document.getElementById('analytics-cookies');
        const marketingCookies = document.getElementById('marketing-cookies');
        const preferenceCookies = document.getElementById('preference-cookies');
        
        // Verificar se o usuário já fez sua escolha de cookies
        const cookieConsent = getCookie('cookie_consent');
        
        if (!cookieConsent) {
            // Se não houver consentimento, mostrar o banner
            cookieBanner.style.display = 'block';
        } else {
            // Se já houver consentimento, aplicar as configurações salvas
            const cookiePreferences = JSON.parse(cookieConsent);
            applyPreferences(cookiePreferences);
        }
        
        // Event Listeners
        acceptAllBtn.addEventListener('click', function() {
            const preferences = {
                essential: true,
                analytics: true,
                marketing: true,
                preference: true
            };
            
            savePreferences(preferences);
            cookieBanner.style.display = 'none';
        });
        
        acceptEssentialBtn.addEventListener('click', function() {
            const preferences = {
                essential: true,
                analytics: false,
                marketing: false,
                preference: false
            };
            
            savePreferences(preferences);
            cookieBanner.style.display = 'none';
        });
        
        settingsBtn.addEventListener('click', function() {
            cookieModal.style.display = 'flex';
        });
        
        modalCloseBtn.addEventListener('click', function() {
            cookieModal.style.display = 'none';
        });
        
        savePreferencesBtn.addEventListener('click', function() {
            const preferences = {
                essential: true, // Sempre ativado
                analytics: analyticsCookies.checked,
                marketing: marketingCookies.checked,
                preference: preferenceCookies.checked
            };
            
            savePreferences(preferences);
            cookieModal.style.display = 'none';
            cookieBanner.style.display = 'none';
        });
        
        // Fechar o modal quando clicar fora dele
        window.addEventListener('click', function(event) {
            if (event.target === cookieModal) {
                cookieModal.style.display = 'none';
            }
        });
        
        // Funções auxiliares
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }
        
        function savePreferences(preferences) {
            // Salvar preferências como cookie por 1 ano
            const expiryDate = new Date();
            expiryDate.setFullYear(expiryDate.getFullYear() + 1);
            
            document.cookie = `cookie_consent=${JSON.stringify(preferences)}; expires=${expiryDate.toUTCString()}; path=/; SameSite=Lax`;
            
            // Aplicar preferências imediatamente
            applyPreferences(preferences);
            
            // Enviar evento de analytics (se permitido)
            if (preferences.analytics) {
                // Inicializar analytics - em produção você chamaria sua função de analytics aqui
                console.log('Analytics inicializados');
            }
        }
        
        function applyPreferences(preferences) {
            // Atualizar checkboxes no modal
            if (analyticsCookies) analyticsCookies.checked = preferences.analytics;
            if (marketingCookies) marketingCookies.checked = preferences.marketing;
            if (preferenceCookies) preferenceCookies.checked = preferences.preference;
            
            // Carregar scripts conforme preferências
            if (preferences.analytics) {
                // Carregar scripts de analytics
                loadAnalytics();
            }
            
            if (preferences.marketing) {
                // Carregar scripts de marketing
                loadMarketing();
            }
        }
        
        function loadAnalytics() {
            // Aqui você carregaria scripts de analytics como Google Analytics
            console.log('Scripts de analytics carregados');
        }
        
        function loadMarketing() {
            // Aqui você carregaria scripts de marketing como Facebook Pixel
            console.log('Scripts de marketing carregados');
        }
    });
</script>
