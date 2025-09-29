import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import "./EditForm.css";

const API_BASE = import.meta.env.DEV ? "http://localhost/ilmo/" : "";

export default function EditForm() {
  const [search] = useSearchParams();
  const token = search.get("token");

  const [formData, setFormData] = useState(null);
  const [state, setState] = useState({ loading: true, error: null, saved: false });

  useEffect(() => {
    if (!token) {
      setState({ loading: false, error: "Token puuttuu.", saved: false });
      return;
    }
    (async () => {
      try {
        const res = await fetch(`${API_BASE}get_ilmoitus.php?token=${encodeURIComponent(token)}`);
        const txt = await res.text();
        let data; try { data = JSON.parse(txt); } catch { data = null; }
        if (!data || data.status !== "ok" || !data.ilmoittautuja) {
          throw new Error("Ilmoittautumista ei löydy.");
        }
        setFormData(data.ilmoittautuja);
        setState({ loading: false, error: null, saved: false });
      } catch (err) {
        setState({ loading: false, error: err.message || "Virhe haettaessa tietoja.", saved: false });
      }
    })();
  }, [token]);

  const onChange = (e) => setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));

  const onSubmit = async (e) => {
    e.preventDefault();
    try {
      const res = await fetch(`${API_BASE}update_ilmoitus.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...formData, token }),
      });
      const txt = await res.text();
      let data; try { data = JSON.parse(txt); } catch { data = null; }
      if (data && data.status === "ok") setState(s => ({ ...s, saved: true, error: null }));
      else throw new Error((data && data.message) || "Päivitys epäonnistui.");
    } catch (err) {
      setState(s => ({ ...s, error: err.message || "Verkkovirhe päivityksessä." }));
    }
  };

  if (state.loading) return <div className="ef-container"><p>Ladataan…</p></div>;
  if (state.error)   return <div className="ef-container"><p className="ef-error">{state.error}</p></div>;
  if (!formData)     return null;

  return (
    <div className="ef-container">
      <div className="ef-card">
        <div className="ef-left">
  <h1 className="ef-title">Muokkaa tietoja</h1>
  {formData.kilpailu_nimi && (
    <p className="ef-competition-name">
    <strong>{formData.kilpailu_nimi}</strong>
    </p>
  )}
  <p>Voit muuttaa tietoja: nimi, syntymäaika, seura ja sähköposti.</p>
</div>
        <div className="ef-right">
          {state.saved ? (
            <div className="ef-success">
              <h2>Tiedot päivitetty</h2>
              <p>Muutoksesi tallennettiin onnistuneesti.</p>
            </div>
          ) : (
            <form onSubmit={onSubmit}>
              <label>Nimi</label>
              <input name="nimi" value={formData.nimi || ""} onChange={onChange} required />

              <label>Syntymäaika</label>
              <input type="date" name="syntymaaika" value={formData.syntymaaika || ""} onChange={onChange} required />

              <label>Seura</label>
              <input name="seura" value={formData.seura || ""} onChange={onChange} />

              <label>Sähköposti</label>
              <input type="email" name="sahkoposti" value={formData.sahkoposti || ""} onChange={onChange} required />

              <button className="ef-btn">Tallenna muutokset</button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
