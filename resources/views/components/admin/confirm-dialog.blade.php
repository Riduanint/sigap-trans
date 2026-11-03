{{-- Modal Universal Konfirmasi Perubahan Data SIGAP-TRANS --}}
<div class="atlas-dialog" id="atlas-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="atlas-confirm-title" aria-describedby="atlas-confirm-message" style="z-index: 999999 !important;">
    <div class="atlas-dialog__panel" style="max-width: 440px; border-radius: 8px; overflow: hidden; box-shadow: 0 20px 45px rgba(14, 42, 71, 0.22);">
        <div class="atlas-dialog__body" style="padding: 26px 24px 20px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px;">
            <div id="atlas-confirm-icon-box" style="width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s ease;">
                <svg id="atlas-confirm-icon" style="width: 26px; height: 26px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"></svg>
            </div>
            <div>
                <h3 id="atlas-confirm-title" style="font-size: 17px; font-weight: 700; color: #0E2A47; margin: 0 0 6px 0; letter-spacing: -0.01em;">Konfirmasi Tindakan</h3>
                <p id="atlas-confirm-message" style="font-size: 13.5px; line-height: 1.5; color: #5a6e7d; margin: 0;">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            </div>
        </div>
        <div class="atlas-dialog__foot" style="display: flex; justify-content: center; gap: 12px; padding: 15px 20px; background: #f8fafc; border-top: 1px solid var(--atlas-line);">
            <button type="button" class="atlas-button" id="atlas-confirm-btn-cancel" style="min-width: 105px;">Batal</button>
            <button type="button" class="atlas-button" id="atlas-confirm-btn-proceed" style="min-width: 125px;">Lanjutkan</button>
        </div>
    </div>
</div>

