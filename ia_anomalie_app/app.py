from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer, util

app = Flask(__name__)
model = SentenceTransformer('all-MiniLM-L6-v2')

@app.route('/api/anomalie', methods=['POST'])
def detect_anomalie():
    data = request.json

    desc_appel = data.get('description_appel', '')
    desc_soumission = data.get('description_soumission', '')
    
    # 🔧 Convertir en float pour éviter erreur TypeError
    prix_propose = float(data.get('prix_propose', 0))
    budget_max = float(data.get('budget_max', 1))
    temps_realisation = float(data.get('temps_realisation', 0))

    vec_appel = model.encode(desc_appel)
    vec_soum = model.encode(desc_soumission)
    score_sim = util.cos_sim(vec_appel, vec_soum).item() * 100

    ecart_prix = abs(prix_propose - budget_max) / max(budget_max, 1)
    score_prix = max(0, 100 - ecart_prix * 100)
    score_delai = 100 if temps_realisation <= 15 else max(0, 100 - (temps_realisation - 15) * 5)
    score_total = (score_sim * 0.5 + score_prix * 0.3 + score_delai * 0.2)

    verdict = "Soumission normale"
    if score_total < 50 or score_sim < 30:
        verdict = "Soumission suspecte"

    return jsonify({
        "similarite_description": round(score_sim, 2),
        "score_prix": round(score_prix, 2),
        "score_delai": round(score_delai, 2),
        "score_total": round(score_total, 2),
        "verdict": verdict,
        "explication": f"Basé sur similarité: {round(score_sim,2)} | prix: {round(score_prix,2)} | délai: {round(score_delai,2)}"
    })

if __name__ == '__main__':
    app.run(debug=True)
