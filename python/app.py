from flask import Flask, request, jsonify
import joblib
import numpy as np

app = Flask(__name__)

# Chargement des objets picklés
model = joblib.load("model.pkl")
scaler = joblib.load("scaler.pkl")
vectorizer = joblib.load("vectorizer.pkl")

@app.route("/predict", methods=["POST"])
def predict():
    data = request.get_json()

    # Variables numériques
    prixPropose = float(data["prixPropose"])
    temps_realisation = float(data["temps_realisation"])

    # Texte à vectoriser
    description = data["description"]

    # Transformation
    numeric_features = scaler.transform([[prixPropose, temps_realisation]])
    text_features = vectorizer.transform([description]).toarray()

    # Fusion des features
    features = np.hstack((numeric_features, text_features))

    # Prédiction
    score = model.predict(features)[0]
    return jsonify({"score_ia": float(score)})

if __name__ == "__main__":
    app.run(debug=True)
