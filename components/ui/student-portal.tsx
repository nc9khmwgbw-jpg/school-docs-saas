"use client";

import { useEffect, useState, type ReactNode } from "react";
import GlyphPortal from "@/components/ui/glyph-portal";

export interface StudentPortalProps {
  /** Nom complet de l'étudiant à afficher */
  studentName?: string;
  /** Mot-clé typographique pour le zoom caméra (ex: "DOCS", "ETUDIANT", "SUBLIME") */
  portalWord?: string;
  /** URL de redirection vers la page des documents ou ancre (#coffre-fort-documents) */
  documentsUrl?: string;
  /** Callback optionnel lors du clic sur le bouton */
  onAccessDocuments?: () => void;
  /** Longueur de défilement (1 à 8) */
  scrollLength?: number;
  /** Liste des documents récents pour prévisualisation */
  documents?: Array<{
    id: string | number;
    title: string;
    date: string;
    status: string;
    downloadUrl?: string;
  }>;
}

const family = '"Glyph Portal Jakarta", "Cinzel", "Playfair Display", Georgia, serif';
let fontLoad: Promise<void> | undefined;

export default function StudentPortal({
  studentName = "Amina Benali",
  portalWord = "DOSSIER",
  documentsUrl = "/etudiant/dashboard#coffre-fort-documents",
  onAccessDocuments,
  scrollLength = 2.4,
  documents = [
    { id: "ATT-2026-01", title: "Attestation de Réussite & Scolarité", date: "14/10/2026", status: "Scellé & Prêt" },
    { id: "REL-2026-02", title: "Relevé de Notes Certifié (Semestre 1)", date: "12/10/2026", status: "Scellé & Prêt" },
    { id: "CERT-2026-03", title: "Certificat d'Inscription Promotion 2026", date: "01/09/2026", status: "Scellé & Prêt" },
  ],
}: StudentPortalProps) {
  const [face, setFace] = useState<string | null>(null);

  useEffect(() => {
    let settled = false;
    const finish = (value: string) => {
      if (!settled) {
        settled = true;
        setFace(value);
      }
    };
    fontLoad ??= new FontFace(
      "Glyph Portal Jakarta",
      'url("https://cdn.21st.dev/assets/mirror/15/153fc85b70298beeb1d61a5f723331649e7f23bb77302a66e61cb3e2fbdb5e79.woff2")',
      { weight: "400 700 900" }
    )
      .load()
      .then((font) => {
        document.fonts.add(font);
      });
    const timeout = window.setTimeout(() => finish("Georgia, serif"), 1600);
    void fontLoad.then(
      () => finish(family),
      () => finish("Georgia, serif")
    );
    return () => {
      settled = true;
      clearTimeout(timeout);
    };
  }, []);

  const handleAction = () => {
    if (onAccessDocuments) {
      onAccessDocuments();
    } else if (documentsUrl) {
      window.location.href = documentsUrl;
    }
  };

  return (
    <div
      data-demo-scroll
      data-student-portal
      tabIndex={0}
      role="region"
      aria-label={`Bonjour ${studentName}. Accédez à vos documents officiels certifiés.`}
      style={{
        width: "100%",
        minHeight: "100svh",
        background: "#090c13",
        color: "#fbf9f5",
        containerType: "inline-size",
        fontFamily: face ?? "Georgia, serif",
      }}
    >
      <style>{`
        [data-student-portal] {
          --portal-brass: #c5a059;
          --portal-brass-bright: #f3d79b;
          --portal-bg: #090c13;
          --portal-card: #0e121c;
          --portal-ink: #fbf9f5;
        }
        [data-student-portal] [data-gp-caption] {
          inset: calc(var(--gp-word-bottom, 50%) + 72px) 24px auto;
          justify-content: center;
        }
        [data-student-portal] [data-gp-hint] {
          display: none;
        }
        [data-student-portal] .btn-access-docs {
          display: inline-flex;
          align-items: center;
          gap: 14px;
          min-height: 50px;
          padding: 0 28px;
          background: linear-gradient(135deg, #c5a059 0%, #997328 100%);
          border: 1px solid rgba(243, 215, 155, 0.4);
          border-radius: 12px;
          color: #07090e;
          font-family: inherit;
          font-size: 14px;
          font-weight: 700;
          letter-spacing: 0.04em;
          text-decoration: none;
          cursor: pointer;
          box-shadow: 0 4px 20px rgba(197, 160, 89, 0.28);
          transition: transform 0.2s, box-shadow 0.2s;
        }
        [data-student-portal] .btn-access-docs:hover {
          transform: translateY(-2px);
          box-shadow: 0 8px 26px rgba(197, 160, 89, 0.45);
        }
        [data-student-portal] .student-header-cluster {
          position: absolute;
          inset: clamp(24px, 4.5cqw, 44px) clamp(24px, 5cqw, 60px) auto;
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 20px;
          z-index: 10;
        }
        [data-student-portal] .greeting-hero-banner {
          position: absolute;
          inset: auto 24px calc(100% - var(--gp-word-top, 35%) + 24px);
          margin: 0;
          text-align: center;
          font-size: clamp(14px, 1.8cqw, 18px);
          font-weight: 600;
          letter-spacing: 0.1em;
          text-transform: uppercase;
          color: var(--portal-brass-bright);
        }
        [data-student-portal] .student-subtitle {
          position: absolute;
          inset: calc(var(--gp-word-bottom, 50%) + 24px) 24px auto;
          margin: 0;
          text-align: center;
          font-size: clamp(15px, 2cqw, 19px);
          color: #a8b0c2;
          font-weight: 400;
        }
        [data-student-portal] .docs-vault-preview {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
          gap: 20px;
          width: 100%;
          max-width: 900px;
          margin-top: 30px;
        }
        [data-student-portal] .doc-vault-item {
          background: rgba(14, 18, 28, 0.85);
          border: 1px solid rgba(197, 160, 89, 0.25);
          border-radius: 14px;
          padding: 22px;
          text-align: left;
          backdrop-filter: blur(10px);
        }
      `}</style>

      {face ? (
        <GlyphPortal
          word={portalWord}
          fontFamily={face}
          fontWeight={900}
          scrollLength={scrollLength}
          interactive={true}
          annotations={false}
          enterLabel="Accéder à vos documents ↘"
          style={{
            "--gp-paper": "#090c13",
            "--gp-ink": "#fbf9f5",
            "--gp-field": "#0c1322",
            "--gp-foreground": "#fbf9f5",
          }}
          front={
            <>
              <header className="student-header-cluster">
                <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                  <span style={{ fontSize: "22px" }}>🏛️</span>
                  <span style={{ fontWeight: 700, fontSize: "16px", letterSpacing: "0.06em", color: "#c5a059" }}>
                    REGISTRE ACADÉMIQUE
                  </span>
                </div>
                <div style={{ fontSize: "12px", color: "#8c94a6", fontFamily: "monospace" }}>
                  SESSION OFFICIELLE 2026
                </div>
              </header>

              <div className="greeting-hero-banner">
                Bonjour, {studentName}
              </div>

              <p className="student-subtitle">
                Votre coffre-fort académique est prêt. Cliquez ci-dessous ou défilez pour consulter et retirer vos pièces scellées.
              </p>

              <div style={{ position: "absolute", inset: "auto 24px 7%", textAlign: "center", color: "#8c94a6", fontSize: "12px" }}>
                <button
                  type="button"
                  onClick={handleAction}
                  className="btn-access-docs"
                >
                  <span>Accéder à vos documents</span>
                  <span aria-hidden="true">&rarr;</span>
                </button>
              </div>
            </>
          }
        >
          {/* Contenu interne après le passage dans le portail */}
          <div style={{ maxWidth: "960px", margin: "0 auto", padding: "40px 20px" }}>
            <div style={{ display: "inline-block", padding: "4px 12px", borderRadius: "999px", background: "rgba(197,160,89,0.15)", border: "1px solid #c5a059", color: "#f3d79b", fontSize: "11px", fontWeight: 700, letterSpacing: "0.1em", textTransform: "uppercase", marginBottom: "14px" }}>
              Documents Disponibles
            </div>
            <h2 style={{ fontSize: "clamp(24px, 4vw, 38px)", margin: 0, fontWeight: 700, color: "#fff" }}>
              Vos documents scellés & certifiés
            </h2>
            <p style={{ color: "#9aa1b2", fontSize: "15px", marginTop: "8px", maxWidth: "550px" }}>
              Tous vos documents sont signés cryptographiquement et munis d'un QR code de vérification publique.
            </p>

            <div className="docs-vault-preview">
              {documents.map((doc) => (
                <div key={doc.id} className="doc-vault-item">
                  <div style={{ fontSize: "11px", color: "#c5a059", fontFamily: "monospace" }}>{doc.id}</div>
                  <h3 style={{ fontSize: "16px", fontWeight: 700, margin: "8px 0 4px", color: "#fff" }}>{doc.title}</h3>
                  <div style={{ fontSize: "12px", color: "#8c94a6", marginBottom: "16px" }}>Émis le {doc.date}</div>
                  <a
                    href={doc.downloadUrl ?? documentsUrl}
                    style={{
                      display: "inline-block",
                      padding: "8px 16px",
                      borderRadius: "8px",
                      background: "rgba(16, 185, 129, 0.2)",
                      border: "1px solid rgba(16, 185, 129, 0.5)",
                      color: "#6ee7b7",
                      fontSize: "12px",
                      fontWeight: 700,
                      textDecoration: "none",
                    }}
                  >
                    Télécharger (PDF) &darr;
                  </a>
                </div>
              ))}
            </div>

            <div style={{ marginTop: "36px" }}>
              <button
                type="button"
                onClick={handleAction}
                className="btn-access-docs"
              >
                <span>Accéder à mon espace complet &rarr;</span>
              </button>
            </div>
          </div>
        </GlyphPortal>
      ) : (
        <div role="status" style={{ height: "100%", display: "grid", placeItems: "center", color: "#9aa1b2", fontSize: 13 }}>
          Chargement du portail académique...
        </div>
      )}
    </div>
  );
}
