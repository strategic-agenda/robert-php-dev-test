import React, { useEffect, useState } from "react";
import {
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  IconButton,
  Button,
  Box,
  Grid,
  Tooltip,
} from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";
import EditIcon from "@mui/icons-material/Edit";
import HistoryIcon from "@mui/icons-material/History";
import ConfirmDialog from "./Alerts/ConfirmDialog";
import AddTranslationModal from "../components/Modals/AddTraslationModal";
import EditTranslationModal from "../components/Modals/EditTranslationModal";
import TranslationHistoryModal from "./Modals/TranslationHistoryModal";

const API_URL = "http://localhost:8000/api/translations.php";
const LANG_API_URL = "http://localhost:8000/api/languages.php";

const TranslationList = () => {
  const [units, setUnits] = useState([]);
  const [languages, setLanguages] = useState([]);
  const [openDialog, setOpenDialog] = useState(false);
  const [unitToDelete, setUnitToDelete] = useState(null);
  const [openAddModal, setOpenAddModal] = useState(false);
  const [openEditModal, setOpenEditModal] = useState(false);
  const [openHistoryModal, setOpenHistoryModal] = useState(false);
  const [editUnit, setEditUnit] = useState(null);
  const [selectedTranslationId, setSelectedTranslationId] = useState(null);

  useEffect(() => {
    fetch(LANG_API_URL)
      .then((res) => res.json())
      .then((data) => setLanguages(data))
      .catch((err) => console.error("Error fetching languages:", err));

    fetch(API_URL)
      .then((res) => res.json())
      .then((data) => setUnits(data.slice(0, 10)))
      .catch((err) => console.error("Error fetching translations:", err));
  }, []);

  const getLangCode = (langId) => {
    const lang = languages.find((l) => l.id === langId);
    return lang ? lang.code : "";
  };

  const confirmDelete = (id) => {
    setUnitToDelete(id);
    setOpenDialog(true);
  };

  const handleDelete = () => {
    fetch(`${API_URL}?id=${unitToDelete}`, {
      method: "DELETE",
    })
      .then((res) => {
        if (res.ok) {
          setUnits(units.filter((u) => u.id !== unitToDelete));
        }
        setOpenDialog(false);
        setUnitToDelete(null);
      })
      .catch((err) => {
        console.error("Failed to delete:", err);
        setOpenDialog(false);
      });
  };

  return (
<>
        <Grid container justifyContent="space-between" alignItems="center" mb={2}>
          <Typography variant="h4" fontWeight="bold">
            Translation Units
          </Typography>
          <Button
            variant="contained"
            onClick={() => setOpenAddModal(true)}
            sx={{ borderRadius: 2 }}
          >
            + Add Translation
          </Button>
        </Grid>

        <TableContainer component={Paper} sx={{ borderRadius: 3 }}>
          <Table stickyHeader>
            <TableHead>
              <TableRow>
                <TableCell><strong>Source</strong></TableCell>
                <TableCell><strong>Language</strong></TableCell>
                <TableCell><strong>Translation</strong></TableCell>
                <TableCell><strong>Version</strong></TableCell>
                <TableCell align="center"><strong>Actions</strong></TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {units.map((unit) => (
                <TableRow key={unit.id} hover>
                  <TableCell>{unit.source_text}</TableCell>
                  <TableCell>{getLangCode(unit.target_lang_id) || "-"}</TableCell>
                  <TableCell>{unit.translated_text || "—"}</TableCell>
                  <TableCell>{unit.version || "-"}</TableCell>
                  <TableCell align="center">
                    <Box display="flex" justifyContent="center" gap={1}>
                      <Tooltip title="View History">
                        <IconButton color="primary" onClick={() => {
                          setSelectedTranslationId(unit.translation_id);
                          setOpenHistoryModal(true);
                        }}>
                          <HistoryIcon />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title="Edit">
                        <IconButton color="success" onClick={() => {
                          setEditUnit(unit);
                          setOpenEditModal(true);
                        }}>
                          <EditIcon />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title="Delete">
                        <IconButton color="error" onClick={() => confirmDelete(unit.id)}>
                          <DeleteIcon />
                        </IconButton>
                      </Tooltip>
                    </Box>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>

        {/* Dialogs & Modals */}
        <ConfirmDialog
          open={openDialog}
          onClose={() => setOpenDialog(false)}
          onConfirm={handleDelete}
          title="Delete Translation?"
          content="Are you sure you want to delete this translation unit?"
        />
        <AddTranslationModal
          open={openAddModal}
          onClose={() => setOpenAddModal(false)}
          onAdd={() => {
            fetch(API_URL)
              .then((res) => res.json())
              .then((data) => setUnits(data.slice(0, 10)));
          }}
        />
        <EditTranslationModal
          open={openEditModal}
          onClose={() => setOpenEditModal(false)}
          unit={editUnit}
          onSave={() => {
            fetch(API_URL)
              .then((res) => res.json())
              .then((data) => setUnits(data.slice(0, 10)));
          }}
        />
        <TranslationHistoryModal
          open={openHistoryModal}
          onClose={() => setOpenHistoryModal(false)}
          translationId={selectedTranslationId}
        />
      </>
  );
};

export default TranslationList;
