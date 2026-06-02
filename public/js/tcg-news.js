document.addEventListener("DOMContentLoaded", () => {
  const mtgContainer = document.getElementById("mtg-news");
  const pokemonContainer = document.getElementById("pokemon-news");

  const mtgUrl = "https://api.scryfall.com/cards/search?q=game:paper&order=released&dir=desc";
  const pokemonUrl = "https://api.pokemontcg.io/v2/cards?q=supertype:Pokémon&pageSize=6&orderBy=-set.releaseDate";

  async function loadMTGNews() {
    if (!mtgContainer) return;

    try {
      const res = await fetch(mtgUrl, {
        headers: {
          Accept: "application/json",
        },
      });

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
      }

      const data = await res.json();

      if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
        mtgContainer.innerHTML = '<p class="text-muted">Aucune nouveauté Magic trouvée.</p>';
        return;
      }

      const cards = data.data.filter((card) => card.image_uris && card.image_uris.normal).slice(0, 4);

      renderMTGCards(cards);
    } catch (error) {
      console.error("Erreur Scryfall:", error);
      mtgContainer.innerHTML = '<p class="text-muted">Erreur lors du chargement des cartes Magic.</p>';
    }
  }

  async function loadPokemonNews() {
    if (!pokemonContainer) return;

    try {
      const res = await fetch(pokemonUrl, {
        headers: {
          Accept: "application/json",
          // "X-Api-Key": "TA_CLE_API"
        },
      });

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
      }

      const data = await res.json();

      if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
        pokemonContainer.innerHTML = '<p class="text-muted">Aucune nouveauté Pokémon trouvée.</p>';
        return;
      }

      const cards = data.data.filter((card) => card.images && card.images.small).slice(0, 4);

      renderPokemonCards(cards);
    } catch (error) {
      console.error("Erreur Pokémon TCG API:", error);
      pokemonContainer.innerHTML = '<p class="text-muted">Erreur lors du chargement des cartes Pokémon.</p>';
    }
  }

  function renderMTGCards(cards) {
    mtgContainer.innerHTML = "";

    cards.forEach((card) => {
      const cardEl = document.createElement("article");
      cardEl.className = "tcg-news-card";

      cardEl.innerHTML = `
        <div class="tcg-news-card__image-wrap">
          <img src="${card.image_uris.normal}" alt="${card.name}" class="tcg-news-card__image" loading="lazy">
        </div>
        <div class="tcg-news-card__content">
          <h3 class="tcg-news-card__title">${card.name}</h3>
          <p class="tcg-news-card__meta">Jeu : Magic</p>
          <p class="tcg-news-card__meta">Set : ${card.set_name ?? "Inconnu"}</p>
          <p class="tcg-news-card__meta">Rareté : ${card.rarity ?? "Inconnue"}</p>
        </div>
      `;

      mtgContainer.appendChild(cardEl);
    });
  }

  function renderPokemonCards(cards) {
    pokemonContainer.innerHTML = "";

    cards.forEach((card) => {
      const cardEl = document.createElement("article");
      cardEl.className = "tcg-news-card";

      cardEl.innerHTML = `
        <div class="tcg-news-card__image-wrap">
          <img src="${card.images.small}" alt="${card.name}" class="tcg-news-card__image" loading="lazy">
        </div>
        <div class="tcg-news-card__content">
          <h3 class="tcg-news-card__title">${card.name}</h3>
          <p class="tcg-news-card__meta">Jeu : Pokémon</p>
          <p class="tcg-news-card__meta">Set : ${card.set?.name ?? "Inconnu"}</p>
          <p class="tcg-news-card__meta">Rareté : ${card.rarity ?? "Inconnue"}</p>
        </div>
      `;

      pokemonContainer.appendChild(cardEl);
    });
  }

  loadMTGNews();
  loadPokemonNews();
});
