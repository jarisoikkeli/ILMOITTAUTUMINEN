import { useState, useEffect } from "react";
import "./RegisterForm.css";
import checkbook from "../assets/checkbook.svg";
import thumb from "../assets/thumb.svg";
import { useNavigate } from "react-router-dom";

const API_BASE = import.meta.env.DEV ? "http://localhost/ilmo/" : "";

const RegisterForm = () => {
  const [formData, setFormData] = useState({
    kilpailu_id: "",
    nimi: "",
    syntymaaika: "",
    seura: "",
    sahkoposti: "",
  });

  const [kilpailut, setKilpailut] = useState([]);
  const [success, setSuccess] = useState(null);

  // Lataustilat
  const [isFetching, setIsFetching] = useState(true);   // kilpailujen haku
  const [isSubmitting, setIsSubmitting] = useState(false); // lomakkeen lähetys
  const isBusy = isFetching || isSubmitting;

  useEffect(() => {
    const fetchKilpailut = async () => {
      try {
        setIsFetching(true);
        const res = await fetch(`${API_BASE}get_kilpailut.php`);
        const data = await res.json();
        setKilpailut(data);
      } catch (err) {
        console.error("Kilpailujen haku epäonnistui:", err);
        alert("Kilpailulistaa ei saatu ladattua.");
      } finally {
        setIsFetching(false);
      }
    };
    fetchKilpailut();
  }, []);

  const handleChange = (e) => {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      const response = await fetch(`${API_BASE}register.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      // Jos PHP palauttaa tekstin eikä JSONia virhetilanteessa:
      const contentType = response.headers.get("content-type") || "";
      if (!contentType.includes("application/json")) {
        const text = await response.text();
        console.error("Ei-JSON vastaus:", text);
        alert("Ilmoittautuminen epäonnistui.");
        return;
      }

      const data = await response.json();
      if (data.status === "ok") {
        setSuccess(data);
      } else {
        alert(data.message || "Ilmoittautuminen epäonnistui");
      }
    } catch (err) {
      console.error(err);
      alert("Virhe ilmoittautumisessa");
    } finally {
      setIsSubmitting(false);
    }
  };

  const resetForm = () => {
    setFormData({
      kilpailu_id: "",
      nimi: "",
      syntymaaika: "",
      seura: "",
      sahkoposti: "",
    });
    setSuccess(null);
  };

  // Poimi valitun kilpailun id linkkiä varten (success-vastauksesta tai lomakedatasta)
  const selectedKilpailuId = success?.kilpailu_id ?? formData.kilpailu_id;

  // ------------------------
  // Onnistumisnäkymä
  // ------------------------
  if (success) {
    return (
      <div className="form-container">
        <div className="form-left">
          <img src={thumb} alt="Ilmoittautuminen onnistui" className="form-icon" />
          <p><strong>Ilmoittautuminen onnistui!</strong></p>
        </div>

        <div className="form-right">
          {/* message sisältää <br>, näytetään HTML:nä */}
          <h2 dangerouslySetInnerHTML={{ __html: success.message }} />

          {success.kilpailu_info && (
            <p
              style={{ whiteSpace: "pre-line" }}
              dangerouslySetInnerHTML={{ __html: success.kilpailu_info }}
            />
          )}

          <div className="btn-group">
            <button className="btn" onClick={resetForm}>
              Tee toinen ilmoittautuminen
            </button>

          {selectedKilpailuId && (
  <a
    className="btn"
    href={`/ilmo/#/lista?kilpailu_id=${encodeURIComponent(selectedKilpailuId)}`}
  >
    Ilmoittautuneiden lista
  </a>
)}
        </div>

        </div>
      </div>
    );
  }

  // ------------------------
  // Lomakenäkymä
  // ------------------------
  return (
    <div className="form-container">
      {/* Lataus-overlay (näkyy sekä haussa että lähetettäessä) */}
      {isBusy && (
        <div className="loading-overlay" role="status" aria-live="polite" aria-busy="true">
          <div className="spinner" />
          <div className="loading-text">
            {isSubmitting ? "Lähetetään ilmoittautumista…" : "Ladataan kilpailulistaa…"}
          </div>
        </div>
      )}

      <div className="form-left" aria-hidden={isBusy}>
        <img
          src={checkbook}
          alt="Ilmoittautumisen symboli"
          className="form-icon"
        />
        <p><strong>Täytä lomakkeen kaikki kentät huolellisesti.</strong></p>
        <p>
          Kun olet lähettänyt ilmoittautumisen, saat maksutiedot heti
          näytöllesi sekä sähköpostiisi.
        </p>
      </div>

      <div className="form-right" aria-hidden={isBusy}>
        <h2>Ilmoittautumislomake</h2>

        <form onSubmit={handleSubmit}>
          <label>Kilpailu</label>
          <select
            name="kilpailu_id"
            value={formData.kilpailu_id}
            onChange={handleChange}
            required
            disabled={isBusy}
          >
            <option value="">
              {isFetching ? "Ladataan kilpailuja…" : "-- Valitse kilpailu --"}
            </option>
            {kilpailut.map((kisa) => (
              <option key={kisa.id} value={kisa.id}>
                {kisa.nimi} ({kisa.ajankohta})
              </option>
            ))}
          </select>

          <label>Nimi</label>
          <input
            type="text"
            name="nimi"
            value={formData.nimi}
            onChange={handleChange}
            required
            disabled={isBusy}
          />

          <label>Syntymäaika</label>
          <input
            type="date"
            name="syntymaaika"
            value={formData.syntymaaika}
            onChange={handleChange}
            required
            disabled={isBusy}
          />

          <label>Seura</label>
          <input
            type="text"
            name="seura"
            value={formData.seura}
            onChange={handleChange}
            disabled={isBusy}
          />

          <label>Sähköposti</label>
          <input
            type="email"
            name="sahkoposti"
            value={formData.sahkoposti}
            onChange={handleChange}
            required
            disabled={isBusy}
          />

          <button type="submit" className="btn" disabled={isBusy}>
            {isSubmitting ? "Lähetetään…" : "Ilmoittaudu"}
          </button>
        </form>
      </div>
    </div>
  );
};

export default RegisterForm;
