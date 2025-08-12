from flask import Flask, request, jsonify
import os
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)

# --------- Fonction de détection des catégories ---------
def detect_categories(data):
    categories = []
    soum = data["soumission"]
    appel = data["appel"]

    # Vérif budget max uniquement si présent
    if appel.get("budget_max") is not None and soum.get("prix") is not None:
        try:
            if float(soum["prix"]) > float(appel["budget_max"]):
                categories.append("budget_over")
        except (ValueError, TypeError):
            pass

    # Vérif délai max uniquement si présent
    if appel.get("delai_max") is not None and soum.get("delai") is not None:
        try:
            if float(soum["delai"]) > float(appel["delai_max"]):
                categories.append("delay_over")
        except (ValueError, TypeError):
            pass

    # Vérif dossier complet
    if not soum.get("dossier_complet", True):
        categories.append("incomplete_file")

    return categories

# --------- Endpoint Flask ---------
@app.route("/generate-explanation", methods=["POST"])
def generate_explanation():
    try:
        data = request.get_json()

        verdict = data.get("verdict", "inconnu")
        categories = detect_categories(data)

        # Phrase publique simple
        public_phrase = f"La soumission est {verdict}. Raisons: {', '.join(categories) if categories else 'aucune'}."

        return jsonify({
            "verdict": verdict,
            "categories": categories,
            "public_phrase": public_phrase
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5002, debug=True)
