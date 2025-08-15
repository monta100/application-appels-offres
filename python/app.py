from flask import Flask, request, jsonify
import numpy as np
import joblib

# Charger le vectorizer (obligatoire pour similarité texte)
vectorizer = joblib.load("vectorizer.pkl")

app = Flask(__name__)

# ======================
#   Hyperparamètres (poids en pourcentage)
# ======================
W_TEXT   = 0.35  # 35% similarité texte
W_PRICE  = 0.40  # 40% adéquation prix
W_DELAY  = 0.25  # 25% adéquation délai

SIGMA_PRICE = 0.20  # tolérance prix ±20%
SIGMA_DELAY = 0.25  # tolérance délai ±25%

COS_LOW  = 0.50
COS_HIGH = 0.90

def clip01(x):
    return max(0.0, min(1.0, x))

def map_cos_to_0_100(cos_val: float) -> float:
    if cos_val <= COS_LOW:  
        return 0.0
    if cos_val >= COS_HIGH: 
        return 100.0
    return (cos_val - COS_LOW) * (100.0 / (COS_HIGH - COS_LOW))

def cosine_similarity_tfidf(desc: str, ref_text: str) -> float:
    X = vectorizer.transform([desc, ref_text])
    a = X[0]
    b = X[1]
    num = a.multiply(b).sum()
    denom = np.sqrt(a.multiply(a).sum()) * np.sqrt(b.multiply(b).sum())
    if denom == 0:
        return 0.0
    return float(num / denom)

def price_score_0_100(prix: float, budget: float | None) -> float:
    if not budget or budget <= 0:
        return 50.0
    ratio = prix / budget
    score = np.exp(-0.5 * ((ratio - 1.0) / SIGMA_PRICE) ** 2) * 100.0
    if ratio < 0.5:
        score *= 0.85
    return float(score)

def delay_score_0_100(jours_proposes: float, jours_attendus: float | None) -> float:
    if not jours_attendus or jours_attendus <= 0:
        return 50.0
    ratio = jours_proposes / jours_attendus
    score = np.exp(-0.5 * ((ratio - 1.0) / SIGMA_DELAY) ** 2) * 100.0
    if ratio < 0.5:
        score *= 0.85
    return float(score)

@app.route("/predict", methods=["POST"])
def predict():
    data = request.get_json(force=True)

    try:
        prix = float(data["prixPropose"])
        jours = float(data["temps_realisation"])
        desc = (data["description"] or "").strip()
    except Exception:
        return jsonify({"error": "Champs requis: prixPropose, temps_realisation, description"}), 400

    ref_text = (data.get("ref_text") or "").strip()
    budget = data.get("budget", None)
    jours_attendus = data.get("jours_attendus", None)

    try:
        budget = float(budget) if budget is not None else None
    except Exception:
        budget = None
    try:
        jours_attendus = float(jours_attendus) if jours_attendus is not None else None
    except Exception:
        jours_attendus = None

    # 1) Similarité texte
    if ref_text:
        cos = cosine_similarity_tfidf(desc, ref_text)
        sim_text = map_cos_to_0_100(cos)
    else:
        sim_text = 50.0

    # 2) Prix
    score_price = price_score_0_100(prix, budget)

    # 3) Délai
    score_delay = delay_score_0_100(jours, jours_attendus)

    # Score final pondéré
    score_final = (
        W_TEXT  * sim_text +
        W_PRICE * score_price +
        W_DELAY * score_delay
    )

    score_final = float(round(clip01(score_final / 100.0) * 100.0, 1))

    return jsonify({
        "score_ia": score_final,
        "breakdown": {
            "similarite": round(sim_text, 1),
            "prix": round(score_price, 1),
            "delai": round(score_delay, 1)
        },
        "model_version": "v2.1.0"
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=True)
