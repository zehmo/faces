package com.faces.app.ui;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.DiffUtil;
import androidx.recyclerview.widget.ListAdapter;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.faces.app.R;
import com.faces.app.data.StudentEntity;

import java.io.File;

public class StudentAdapter extends ListAdapter<StudentEntity, StudentAdapter.ViewHolder> {

    public interface OnStudentClickListener {
        void onClick(StudentEntity student);
    }

    private final OnStudentClickListener listener;

    public StudentAdapter(OnStudentClickListener listener) {
        super(DIFF_CALLBACK);
        this.listener = listener;
    }

    private static final DiffUtil.ItemCallback<StudentEntity> DIFF_CALLBACK =
            new DiffUtil.ItemCallback<StudentEntity>() {
                @Override
                public boolean areItemsTheSame(@NonNull StudentEntity a, @NonNull StudentEntity b) {
                    return a.localId == b.localId;
                }

                @Override
                public boolean areContentsTheSame(@NonNull StudentEntity a, @NonNull StudentEntity b) {
                    return a.regNumber.equals(b.regNumber)
                            && a.fullName.equals(b.fullName);
                }
            };

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_student, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        StudentEntity student = getItem(position);
        holder.tvRegNumber.setText(student.regNumber);
        holder.tvFullName.setText(student.fullName);
        holder.tvDepartment.setText(student.department != null ? student.department : "");
        holder.tvLevel.setText(student.level != null ? student.level + "L" : "");

        // Load thumbnail
        if (student.photoLocalPath != null && new File(student.photoLocalPath).exists()) {
            Glide.with(holder.itemView.getContext())
                    .load(new File(student.photoLocalPath))
                    .circleCrop()
                    .placeholder(R.drawable.ic_person)
                    .into(holder.imgThumb);
        } else {
            holder.imgThumb.setImageResource(R.drawable.ic_person);
        }

        holder.itemView.setOnClickListener(v -> listener.onClick(student));
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        final ImageView imgThumb;
        final TextView tvRegNumber;
        final TextView tvFullName;
        final TextView tvDepartment;
        final TextView tvLevel;

        ViewHolder(@NonNull View itemView) {
            super(itemView);
            imgThumb = itemView.findViewById(R.id.imgThumb);
            tvRegNumber = itemView.findViewById(R.id.tvRegNumber);
            tvFullName = itemView.findViewById(R.id.tvFullName);
            tvDepartment = itemView.findViewById(R.id.tvDepartment);
            tvLevel = itemView.findViewById(R.id.tvLevel);
        }
    }
}
