import { useEffect, useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import "./PaidList.css";

const API_BASE = import.meta.env.DEV ? "http://localhost/ilmo/" : "";

export default function PaidList() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const kilpailuId = searchParams.get("kilpailu_id");

  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState(null);
  const [data, setData] = useState(null);

  useEffect(() => {
    let abort = false;

    async function fetchData() {
      if (!kilpailuId) {
        setLoading(false);
        setErr("Puuttuva kilpailu_id.");
        setData(null);
        return;
      }
      setLoading(true);
      setErr(null);

      try {
        const res = await fetch(`${API_BASE}get_maksaneet.php?kilpailu_id=${encodeURIComponent(kilpailuId)}`);
        const json = await res.json();
        if (abort) return;

        if (json.status !== "ok") {
          setErr(json.message || "Haku epäonnistui.");
          setData(null);
        } else {
          setData(json);
        }
      } catch {
        if (!abort) {
          setErr("Verkkovirhe.");
          setData(null);
        }
      } finally {
        if (!abort) setLoading(false);
      }
    }

    fetchData();
    return () => { abort = true; };
  }, [kilpailuId]);

  return (
    <div className="pl-wrap">
      <div className="pl-card" role="region" aria-live="polite">
        {/* Header */}
        <div className="pl-header">
          <div className="pl-title">
            <h1>{data?.kilpailu?.nimi || "Ilmoittautuneiden lista"}</h1>
            {data?.kilpailu?.ajankohta && <div className="pl-date">{data.kilpailu.ajankohta}</div>}
            {typeof data?.count === "number" && (
              <div className="pl-meta"><p>Ilmoittautuneet: <strong>{data.count}</strong></p>
              <p>Lista ei päivity automaattisesti. Nimi lisätään listaan kun osallistumismaksu on maksettu</p></div>
            )}
          </div>
          <div className="pl-actions">
            <button className="btn pl-btn" type="button" onClick={() => navigate(-1)}>Takaisin</button>
          </div>
        </div>

        {/* Body */}
        <div className="pl-body">
          {loading ? (
            <div className="pl-info">Haetaan tietoja…</div>
          ) : err ? (
            <div className="pl-error" role="alert">Virhe: {err}</div>
          ) : (
            <div className="pl-table-wrap">
              <table className="pl-table">
                <thead>
                  <tr>
                    <th style={{width: 80}}>#</th>
                    <th>Nimi</th>
                    <th>Seura</th>
                  </tr>
                </thead>
                <tbody>
                  {data.rows.length === 0 ? (
                    <tr>
                      <td className="pl-empty" colSpan={3}>Listaan ei ole vielä lisätty ilmoittautuneita.</td>
                    </tr>
                  ) : (
                    data.rows.map((r, idx) => (
                      <tr key={`${r.kilpailunumero}-${idx}`}>
                        <td><span className="pl-pill">{r.kilpailunumero}</span></td>
                        <td>{r.nimi}</td>
                        <td>{r.seura || ""}</td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
