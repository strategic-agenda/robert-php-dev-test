import {Link} from 'react-router-dom';

export default function Header() {
  return (
    <div className="header bg-light">
        <div className="container">
        <nav className="navbar navbar-expand-lg navbar-light">
            <div className="container-fluid">
                <Link to="/" className="navbar-brand">Intobi</Link>
                <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span className="navbar-toggler-icon"></span>
                </button>
                <div className="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                        <li className="nav-item">
                            <Link to="/" className="nav-link">Home</Link>
                        </li>
                        <li className="nav-item">
                            <Link to="/translator" className="nav-link fw-bold">Translator</Link>
                        </li>
                        <li className="nav-item">
                            <Link to="/tranlation/create" className="nav-link">Add tranlation</Link>
                        </li>
                        <li className="nav-item">
                            <Link to="/language/add" className="nav-link">Add Language</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        </div>
    </div>
  );
}
