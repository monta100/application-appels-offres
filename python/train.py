# train.py
import pandas as pd
import joblib
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split

# ================================
# 1. Charger ou créer les données
# ================================
# Exemple fictif : remplacer par ton CSV réel
data = pd.DataFrame([
    {
        "prixPropose": 1200,
        "temps_realisation": 25,
        "description": "Développement Laravel et Vue.js complet",
        "score": 70
    },
    {
        "prixPropose": 900,
        "temps_realisation": 20,
        "description": "Création site vitrine responsive",
        "score": 85
    },
    {
        "prixPropose": 2000,
        "temps_realisation": 40,
        "description": "Application e-commerce avec paiement intégré",
        "score": 60
    }
    # ➡️ Ajoute toutes tes données ici
])

# Variables
X_num = data[["prixPropose", "temps_realisation"]]
X_text = data["description"]
y = data["score"]

# ================================
# 2. Prétraitement
# ================================
# Vectorisation du texte
vectorizer = TfidfVectorizer()
X_text_vec = vectorizer.fit_transform(X_text).toarray()

# Normalisation des variables numériques
scaler = StandardScaler()
X_num_scaled = scaler.fit_transform(X_num)

# Fusion
import numpy as np
X = np.hstack((X_num_scaled, X_text_vec))

# ================================
# 3. Entraînement du modèle
# ================================
model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X, y)

# ================================
# 4. Sauvegarde des objets
# ================================
joblib.dump(vectorizer, "vectorizer.pkl")
joblib.dump(scaler, "scaler.pkl")
joblib.dump(model, "model.pkl")

print("✅ vectorizer.pkl, scaler.pkl et model.pkl régénérés avec succès !")
